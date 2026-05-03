<?php

/**
 * ============================================================
 * FITXER: config/jwt.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configuració del paquet tymon/jwt-auth. Defineix la clau
 *   secreta amb la qual es signen els tokens, el temps de vida
 *   (TTL) i la política de llista negra (blacklist). 
 *
 * DETALLS TÈCNICS:
 *   - Els valors crítics es guarden al fitxer .env per seguretat.
 *   - La clau JWT_SECRET es genera amb: php artisan jwt:secret.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per: config/auth.php (guard 'api' → driver 'jwt').
 *   → Usat per: AuthController per gestionar el login/logout.
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Clau Secreta de JWT
    |--------------------------------------------------------------------------
    |
    | Aquesta clau s'utilitza per signar els vostres tokens. Hauria de ser 
    | una cadena aleatòria i secreta. Es recomana generar-la amb l'ordre 
    | d'Artisan proporcionada pel paquet.
    |
    */

    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Claus d'Autenticació Asimètrica
    |--------------------------------------------------------------------------
    |
    | Si feu servir un algoritme asimètric com RS256, haureu de configurar 
    | les claus pública i privada aquí.
    |
    */

    'keys' => [
        'public'     => env('JWT_PUBLIC_KEY'),
        'private'    => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Temps de Vida del Token (TTL)
    |--------------------------------------------------------------------------
    |
    | Defineix el temps (en minuts) que el token serà vàlid. 
    | Per defecte són 120 minuts (2 hores).
    |
    */

    'ttl' => (int) env('JWT_TTL', 120),

    /*
    |--------------------------------------------------------------------------
    | TTL de Renovació (Refresh TTL)
    |--------------------------------------------------------------------------
    |
    | Defineix el temps (en minuts) que un token es pot renovar. 
    | Per defecte són 2 setmanes.
    |
    */

    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | Algoritme de Signatura
    |--------------------------------------------------------------------------
    |
    | L'algoritme utilitzat per signar el token. HS256 és el més comú 
    | per a aplicacions senzilles.
    |
    */

    'algo' => env('JWT_ALGO', Tymon\JWTAuth\Providers\JWT\Provider::ALGO_HS256),

    /*
    |--------------------------------------------------------------------------
    | Claims Obligatoris
    |--------------------------------------------------------------------------
    |
    | Aquests claims han d'estar presents al token per a que sigui considerat vàlid.
    |
    */

    'required_claims' => [
        'iss', 'iat', 'exp', 'nbf', 'sub', 'jti',
    ],

    'persistent_claims' => [],

    'lock_subject' => true,

    'leeway' => (int) env('JWT_LEEWAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Llista Negra (Blacklist)
    |--------------------------------------------------------------------------
    |
    | Si està habilitada, els tokens invalidats (per logout) es guardaran 
    | a la llista negra i no podran ser reutilitzats.
    |
    */

    'blacklist_enabled'      => env('JWT_BLACKLIST_ENABLED', true),
    'blacklist_grace_period' => (int) env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    'decrypt_cookies' => false,

    /*
    |--------------------------------------------------------------------------
    | Proveïdors del Paquet
    |--------------------------------------------------------------------------
    |
    | Defineix les classes encarregades de les diverses funcionalitats del paquet.
    |
    */

    'providers' => [
        'jwt'     => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth'    => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];