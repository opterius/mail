<?php

namespace App\Services;

/**
 * Sanitises incoming email HTML before it's rendered to the user.
 *
 * Two privacy concerns handled in one DOM pass:
 *
 * 1. Spy pixels: <img> tags with width/height <= 2px are stamped by
 *    senders to know when an email was opened, when, from where, and
 *    sometimes by what device. We replace the src with a transparent
 *    1x1 data URI so the request never goes upstream - the sender
 *    gets zero signal. The client-side spy pixel banner counter
 *    increments from the same DOM marker.
 *
 * 2. Tracking links: utm_*, fbclid, gclid, mc_eid, _hsenc, etc are
 *    sender-side analytics parameters. We strip them from <a> hrefs.
 *    Some senders wrap the entire real URL inside a tracker redirect
 *    (mailchimp's click.* domain, hubspot's hs-, sendgrid's
 *    sendgrid.net/wf/click). We unwrap a small set of well-known
 *    wrappers when the real destination is encoded in a `url=`-style
 *    query param.
 *
 * Stats are exposed on the public counters so the view can show the
 * "12 trackers blocked" badge.
 */
class ContentSanitizer
{
    /** Number of spy pixels neutralised in the last sanitize() call. */
    public int $spyPixelCount = 0;

    /** Number of links cleaned (params stripped or unwrapped). */
    public int $cleanedLinkCount = 0;

    /** Sender-side tracking params that get stripped from every link. */
    private const TRACKING_PARAMS = [
        // Google Analytics
        'utm_source','utm_medium','utm_campaign','utm_term','utm_content',
        'utm_id','utm_name','utm_brand','utm_social','utm_social-type',
        // Facebook
        'fbclid','fb_action_ids','fb_action_types','fb_source','fb_ref',
        // Google
        'gclid','gclsrc','dclid','wbraid','gbraid',
        // Microsoft / Bing
        'msclkid',
        // Mailchimp
        'mc_cid','mc_eid','mc_tc',
        // HubSpot
        '_hsenc','_hsmi','__hssc','__hstc','__hsfp','hsCtaTracking',
        // Hotjar / Mixpanel
        'hjid','mp_distinct_id',
        // Twitter / X
        'twclid',
        // Klaviyo
        '_kx','_ke',
        // Drip
        '__s',
        // Generic newsletter trackers
        'ref','referrer','source','campaign','medium',
        // Yandex
        'yclid',
        // Adobe
        'icid','s_cid',
    ];

    /**
     * Hostname pattern => query param that holds the real URL.
     * Used to unwrap tracking redirector links.
     */
    private const URL_WRAPPERS = [
        // Mailchimp & friends
        '/^.*\.list-manage\.com$/i'   => 'url',
        '/^click\..*$/i'              => 'url',
        // SendGrid
        '/^.*\.sendgrid\.net$/i'      => 'url',
        // HubSpot
        '/^.*\.hubspotlinks\.com$/i'  => 'url',
        // Substack
        '/^.*\.substack\.com$/i'      => 'url',
        // Generic Google redirect
        '/^www\.google\.com$/i'       => 'url',
        '/^l\.facebook\.com$/i'       => 'u',
        '/^l\.instagram\.com$/i'      => 'u',
    ];

    /** 1x1 transparent GIF used to neutralise spy pixels. */
    private const BLANK_PIXEL = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function sanitize(string $html): string
    {
        $this->spyPixelCount    = 0;
        $this->cleanedLinkCount = 0;

        if ($html === '' || stripos($html, '<') === false) {
            return $html;
        }

        // DOMDocument with libxml internals so we don't pollute global PHP
        // error state on malformed inbound HTML.
        $prev = libxml_use_internal_errors(true);
        $dom  = new \DOMDocument('1.0', 'UTF-8');

        // Force UTF-8 and let DOM build whatever it can from imperfect input.
        $loadOk = $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_NONET | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (!$loadOk) return $html;

        $this->killSpyPixels($dom);
        $this->cleanLinks($dom);

        $out = $dom->saveHTML();
        return is_string($out) ? $out : $html;
    }

    private function killSpyPixels(\DOMDocument $dom): void
    {
        $imgs = $dom->getElementsByTagName('img');
        // Iterate snapshot so DOM mutation during loop is safe.
        $list = iterator_to_array($imgs);

        foreach ($list as $img) {
            /** @var \DOMElement $img */
            $w = (int) ($img->getAttribute('width')  ?: 99);
            $h = (int) ($img->getAttribute('height') ?: 99);

            // Tracking pixels are typically declared as 1x1, but some
            // senders use 0x0 and let CSS size them. The naturalWidth
            // check on the client side catches CSS-hidden ones; here
            // we only handle the declared-dimension case.
            if ($w <= 2 && $h <= 2 && $w >= 0 && $h >= 0) {
                $img->setAttribute('src', self::BLANK_PIXEL);
                $img->setAttribute('data-was-spy-pixel', '1');
                $img->setAttribute('style', 'display:none!important');
                $this->spyPixelCount++;
            }
        }
    }

    private function cleanLinks(\DOMDocument $dom): void
    {
        $anchors = iterator_to_array($dom->getElementsByTagName('a'));
        foreach ($anchors as $a) {
            /** @var \DOMElement $a */
            $href = $a->getAttribute('href');
            if ($href === '' || $href[0] === '#' || stripos($href, 'mailto:') === 0) continue;

            $cleaned = $this->cleanUrl($href);
            if ($cleaned !== null && $cleaned !== $href) {
                $a->setAttribute('href', $cleaned);
                $a->setAttribute('data-was-tracker', '1');
                $this->cleanedLinkCount++;
            }
        }
    }

    /**
     * Returns the cleaned URL, or null if the URL is unparsable.
     * Strips tracking params from the query string AND unwraps
     * single-level tracker redirector hosts.
     */
    public function cleanUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false) return null;

        // 1. Try to unwrap a redirector. If host matches a wrapper and
        // the wrapper's URL param decodes to a real http(s):// URL,
        // recurse to clean that URL too.
        $host = strtolower($parts['host'] ?? '');
        if ($host && isset($parts['query'])) {
            foreach (self::URL_WRAPPERS as $pattern => $param) {
                if (preg_match($pattern, $host)) {
                    parse_str($parts['query'], $q);
                    if (!empty($q[$param]) && is_string($q[$param])) {
                        $candidate = urldecode($q[$param]);
                        if (preg_match('~^https?://~i', $candidate)) {
                            $inner = $this->cleanUrl($candidate);
                            return $inner ?? $candidate;
                        }
                    }
                }
            }
        }

        // 2. Strip tracking params from this URL's own query.
        if (!isset($parts['query']) || $parts['query'] === '') return $url;

        parse_str($parts['query'], $params);
        $kept = [];
        foreach ($params as $k => $v) {
            if (in_array(strtolower($k), self::TRACKING_PARAMS, true)) continue;
            $kept[$k] = $v;
        }
        if (count($kept) === count($params)) return $url; // nothing stripped

        $newQuery = http_build_query($kept);
        return $this->buildUrl($parts, $newQuery);
    }

    private function buildUrl(array $parts, string $query): string
    {
        $scheme = $parts['scheme'] ?? 'http';
        $user   = $parts['user'] ?? '';
        $pass   = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $auth   = $user !== '' ? $user . $pass . '@' : '';
        $host   = $parts['host'] ?? '';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path   = $parts['path'] ?? '';
        $frag   = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        $q      = $query !== '' ? '?' . $query : '';
        return "{$scheme}://{$auth}{$host}{$port}{$path}{$q}{$frag}";
    }
}
