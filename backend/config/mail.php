<?php

/**
 * ============================================================
 * FITXER: config/mail.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configura els serveis d'enviament de correu electrònic. 
 *   Tot i que en el projecte XOXEMONS l'enviament de correu 
 *   està configurat per defecte al 'log' (per a proves), 
 *   aquí es defineixen els drivers per a SMTP, Mailgun, etc.
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Gestor de Correu per Defecte
    |--------------------------------------------------------------------------
    |
    | Defineix el servei que s'utilitzarà per defecte per enviar correus.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Configuracions dels Gestors de Correu
    |--------------------------------------------------------------------------
    |
    | Aquí podeu configurar tots els gestors de correu i els seus paràmetres.
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'resend' => [
            'transport' => 'resend',
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
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Adreça "From" Global
    |--------------------------------------------------------------------------
    |
    | Defineix l'adreça i el nom que s'utilitzaran per defecte com a remitent.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'XOXEMONS'),
    ],

];
