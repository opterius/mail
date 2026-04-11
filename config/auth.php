<?php

use App\Models\Admin;

return [

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'admins',
    ],

    'guards' => [
        // Webmail users — authenticated against IMAP, no local user table.
        'web' => [
            'driver'   => 'imap',
            'provider' => null,
        ],

        // Admin users — standard session guard backed by the local admins table.
        'admin' => [
            'driver'   => 'session',
            'provider' => 'admins',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model'  => Admin::class,
        ],
    ],

    'passwords' => [
        'admins' => [
            'provider'  => 'admins',
            'table'     => 'admin_password_reset_tokens',
            'expire'    => 60,
            'throttle'  => 60,
        ],
    ],

    'password_timeout' => 10800,

];
