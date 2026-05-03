<?php

/**
 * ============================================================
 * FITXER: config/services.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest fitxer s'utilitza per emmagatzemar les credencials de 
 *   serveis de tercers (com AWS, Mailgun, Slack, etc.). 
 *   Proporciona una ubicació centralitzada per a la configuració 
 *   de serveis externs que l'aplicació XOXEMONS pugui necessitar.
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Serveis de Tercers
    |--------------------------------------------------------------------------
    |
    | Aquí es guarden les credencials dels serveis externs. 
    | Es recomana llegir els valors directament del fitxer .env.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
