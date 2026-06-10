<?php

namespace App\Services;

use App\Models\KnownSender;
use App\Models\SenderStat;

/**
 * Computes a small, interpretable trust badge for a sender, shown
 * next to the From line in the message view.
 *
 * Inputs (in priority order):
 * 1. Explicit user action: KnownSender approved -> 'trusted'
 *                          KnownSender blocked  -> 'blocked'
 * 2. SenderStat counters: replied_count, sent_to_count, received_count,
 *                         deleted_unread_count, marked_spam_count
 * 3. None of the above -> 'unknown'
 *
 * The badge object always has:
 *   level:  trusted|familiar|new|risky|blocked|unknown
 *   label:  short user-facing string
 *   reason: one-sentence justification (shown in tooltip)
 *   counts: raw numbers for the popover detail
 */
class SenderReputation
{
    public function badgeFor(string $userEmail, string $senderEmail): array
    {
        $userEmail   = strtolower(trim($userEmail));
        $senderEmail = strtolower(trim($senderEmail));

        // 1. Explicit allow/block list wins.
        if (KnownSender::isBlocked($userEmail, $senderEmail)) {
            return $this->level('blocked', 'Blocked',
                'You explicitly blocked this sender.', []);
        }
        $explicitlyApproved = KnownSender::isApproved($userEmail, $senderEmail);

        $stat = SenderStat::for($userEmail, $senderEmail);

        // Fully unknown - no stats yet.
        if (!$stat) {
            if ($explicitlyApproved) {
                return $this->level('trusted', 'Trusted',
                    'You approved this sender via the Screener.', []);
            }
            return $this->level('unknown', 'New sender',
                'You have never received mail from this sender before.', []);
        }

        $counts = [
            'received'        => $stat->received_count,
            'replied'         => $stat->replied_count,
            'sent_to'         => $stat->sent_to_count,
            'deleted_unread'  => $stat->deleted_unread_count,
            'marked_spam'     => $stat->marked_spam_count,
            'snoozed'         => $stat->snoozed_count,
        ];

        // 2. Strong negative signals - take precedence over positives.
        if ($stat->marked_spam_count >= 1) {
            return $this->level('risky', 'Flagged',
                'You marked at least one message from this sender as spam.', $counts);
        }
        $deletedRatio = $stat->received_count > 0
            ? $stat->deleted_unread_count / $stat->received_count
            : 0;
        if ($stat->received_count >= 10 && $deletedRatio >= 0.7) {
            return $this->level('risky', 'Mostly deleted',
                "{$stat->deleted_unread_count} of {$stat->received_count} messages from this sender went straight to trash unread.",
                $counts);
        }

        // 3. Strong positive: you've replied to them, or sent to them.
        if ($stat->replied_count >= 3 || $stat->sent_to_count >= 3) {
            return $this->level('trusted', 'Trusted',
                "You have a two-way conversation with this sender ({$stat->replied_count} replies, {$stat->sent_to_count} sent to).",
                $counts);
        }
        if ($explicitlyApproved) {
            return $this->level('trusted', 'Trusted',
                'You approved this sender via the Screener.', $counts);
        }

        // 4. Familiar but one-sided (newsletter, transactional).
        if ($stat->received_count >= 5) {
            return $this->level('familiar', 'Familiar',
                "You have received {$stat->received_count} messages from this sender.", $counts);
        }

        // 5. Recently new.
        return $this->level('new', 'New-ish sender',
            "First seen {$stat->first_seen_at?->diffForHumans()}. {$stat->received_count} messages so far.",
            $counts);
    }

    private function level(string $level, string $label, string $reason, array $counts): array
    {
        return [
            'level'  => $level,
            'label'  => $label,
            'reason' => $reason,
            'counts' => $counts,
        ];
    }
}
