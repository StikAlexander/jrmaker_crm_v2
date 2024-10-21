<?php

return [

    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            // Aquí se ajusta para usar las variables de entorno o Hostinger como predeterminado
            'host' => env('MAIL_HOST', 'smtp.hostinger.com'),
            'port' => env('MAIL_PORT', 465),  // Puerto 465 para Hostinger (SSL)
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'),  // SSL para Hostinger
            'username' => env('MAIL_USERNAME', 'operaciones@jrmaker.com.co'),  // Tu usuario de Hostinger
            'password' => env('MAIL_PASSWORD', 'Metroidvania22.'),  // Tu contraseña de Hostinger
            'timeout' => null,
            'auth_mode' => null,
        ],

        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'operaciones@jrmaker.com.co'),
            'name' => env('MAIL_FROM_NAME', 'J.R. MAKER S.A.S.'),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'operaciones@jrmaker.com.co'),
        'name' => env('MAIL_FROM_NAME', 'J.R. MAKER S.A.S.'),
    ],

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
