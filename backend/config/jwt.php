<?php

/**
 * ============================================================
 * FITXER: config/jwt.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configuració del paquet tymon/jwt-auth. Defineix la clau
 *   secreta amb la qual es signen els tokens, el temps de vida
 *   (TTL) i la política de blacklist. Els valors crítics es
 *   guarden al fitxer .env per seguretat.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per: config/auth.php (guard 'api' → driver 'jwt')
 *   → Usat per: AuthController (respondWithToken usa getTTL())
 *   → La clau JWT_SECRET es genera amb: php artisan jwt:secret
 * ============================================================
 */

return [

    // Clau secreta per signar els tokens HS256. Mai s'ha de compartir.
    // Es desa a .env com JWT_SECRET i es genera amb 'php artisan jwt:secret'.
    'secret' => env('JWT_SECRET'),

    'keys' => [
        'public'     => env('JWT_PUBLIC_KEY'),
        'private'    => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    // Temps de vida del token en minuts (per defecte 120 min = 2 hores).
    // AuthController multiplica per 60 per retornar-lo en segons a Angular.
    'ttl' => (int) env('JWT_TTL', 120),

    // Temps durant el qual un token refresh és vàlid (per defecte 14 dies).
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 20160),

    // Algoritme de signatura. HS256 (HMAC SHA-256) és simètric:
    // usa la mateixa clau per signar i verificar.
    'algo' => env('JWT_ALGO', Tymon\JWTAuth\Providers\JWT\Provider::ALGO_HS256),

    'required_claims' => [
        'iss', 'iat', 'exp', 'nbf', 'sub', 'jti',
    ],

    'persistent_claims' => [],

    'lock_subject' => true,

    'leeway' => (int) env('JWT_LEEWAY', 0),

    // Blacklist habilitada: quan l'usuari fa logout(), el token s'invalida
    // i no es pot reutilitzar, fins i tot si no ha caducat.
    'blacklist_enabled'      => env('JWT_BLACKLIST_ENABLED', true),
    'blacklist_grace_period' => (int) env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    'decrypt_cookies' => false,

    'providers' => [
        'jwt'     => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth'    => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];