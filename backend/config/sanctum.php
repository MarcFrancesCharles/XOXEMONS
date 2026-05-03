<?php

use Laravel\Sanctum\Sanctum;

/**
 * ============================================================
 * FITXER: config/sanctum.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configura Laravel Sanctum, un sistema d'autenticació lleuger 
 *   per a SPAs i APIs basades en tokens simples. 
 *
 * NOTA DEL PROJECTE:
 *   XOXEMONS utilitza principalment JWT (tymon/jwt-auth) per a 
 *   l'autenticació de la API d'Angular. Sanctum es manté per 
 *   defecte o per a possibles integracions futures que no 
 *   requereixin la complexitat de JWT.
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Dominis amb Estat (Stateful Domains)
    |--------------------------------------------------------------------------
    |
    | Les peticions d'aquests dominis rebran galetes d'autenticació 
    | d'API amb estat.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
        // Sanctum::currentRequestHost(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Guards de Sanctum
    |--------------------------------------------------------------------------
    |
    | Aquest array conté els guards d'autenticació que es comprovaran 
    | quan Sanctum intenti autenticar una petició.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Minuts d'Expiració
    |--------------------------------------------------------------------------
    |
    | Aquest valor controla el nombre de minuts fins que un token emès 
    | es consideri caducat.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Prefix del Token
    |--------------------------------------------------------------------------
    |
    | Sanctum pot afegir un prefix als nous tokens per aprofitar 
    | les iniciatives d'escaneig de seguretat.
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Middleware de Sanctum
    |--------------------------------------------------------------------------
    |
    | Quan s'autentica la vostra SPA, és possible que hagueu de 
    | personalitzar part del middleware que utilitza Sanctum.
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
