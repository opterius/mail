<?php

/**
 * Opterius Mail - Open source webmail.
 * Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 * https://opterius.com
 *
 * Copyright (c) Host Server SRL (Opterius)
 * @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 * @author   Iosif Gabriel Chimilevschi <office@opterius.com>
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('mail-ui.admin_allowed_ips', []);

        if (empty($allowed)) {
            return $next($request);
        }

        if (in_array($request->ip(), $allowed, true)) {
            return $next($request);
        }

        abort(403, 'Access denied.');
    }
}
