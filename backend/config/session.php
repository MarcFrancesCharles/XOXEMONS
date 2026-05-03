<?php

use Illuminate\Support\Str;

/**
 * ============================================================
 * FITXER: config/session.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configura la gestió de sessions de l'usuari. Defineix on 
 *   es guarden les dades de sessió, quant de temps duren i 
 *   quines propietats tenen les galetes (cookies) de sessió.
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Driver de Sessió per Defecte
    |--------------------------------------------------------------------------
    |
    | Defineix on s'emmagatzemaran les dades de sessió. En aquest 
    | projecte s'utilitza 'database' per defecte.
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Vida de la Sessió
    |--------------------------------------------------------------------------
    |
    | Aquí podeu especificar el nombre de minuts que voleu que la sessió 
    | romangui inactiva abans de caducar.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Encriptació de la Sessió
    |--------------------------------------------------------------------------
    |
    | Aquesta opció permet especificar que totes les dades de sessió 
    | s'han d'encriptar abans de ser emmagatzemades.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Ubicació dels Fitxers de Sessió
    |--------------------------------------------------------------------------
    |
    | Quan s'utilitza el driver 'file', les dades es guarden al disc.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Connexió i Taula de Base de Dades per a la Sessió
    |--------------------------------------------------------------------------
    |
    | Configura la connexió i la taula quan s'utilitza el driver 'database'.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Magatzem de Cache per a la Sessió
    |--------------------------------------------------------------------------
    |
    | S'utilitza quan s'escull un backend basat en cache (Redis, Memcached).
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Loteria de Neteja de Sessions
    |--------------------------------------------------------------------------
    |
    | Probabilitats que es netegin les sessions antigues de l'emmagatzematge.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nom de la Galeta de Sessió
    |--------------------------------------------------------------------------
    |
    | Podeu canviar el nom de la galeta que utilitza el framework.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Camí i Domini de la Galeta
    |--------------------------------------------------------------------------
    |
    | Determina el camí i el domini per als quals la galeta estarà disponible.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Galetes Només HTTPS
    |--------------------------------------------------------------------------
    |
    | Si s'estableix a true, la galeta només s'enviarà a través de connexions HTTPS.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Només Accés HTTP
    |--------------------------------------------------------------------------
    |
    | Evita que JavaScript pugui accedir al valor de la galeta.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Galetes Same-Site
    |--------------------------------------------------------------------------
    |
    | Determina com es comporten les galetes en peticions entre llocs (cross-site).
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
