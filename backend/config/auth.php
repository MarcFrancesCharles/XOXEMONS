<?php

/**
 * ============================================================
 * FITXER: config/auth.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configura els sistemes d'autenticació de Laravel. En aquest
 *   projecte, l'element crític és el guard 'api' amb driver JWT,
 *   que permet autenticar cada petició a la API comprovant el
 *   token JWT de la capçalera Authorization.
 *
 * MAPA DE CONNEXIONS:
 *   → Usada per: middleware 'auth:api' (definit a routes/api.php)
 *   → Depèn de: config/jwt.php (la clau secreta i TTL del token)
 *   → Afecta: tots els controladors que usen auth('api') o Auth::user()
 * ============================================================
 */

return [

    'defaults' => [
        // El guard per defecte és 'web' (sessió), però les rutes API usen 'api' explícitament.
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Guard 'api': intercepta les peticions amb 'auth:api',
        // llegeix el token JWT de la capçalera, el valida i injecta l'usuari.
        // El driver 'jwt' el proveeix el paquet tymon/jwt-auth.
        'api' => [
            'driver'   => 'jwt',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        // El provider 'users' usa Eloquent amb el model User per trobar
        // l'usuari identificat al "sub" del payload JWT.
        'users' => [
            'driver' => 'eloquent',
            'model'  => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    'passwords' => [
        'users' => [
            'provider'  => 'users',
            'table'     => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'    => 60,
            'throttle'  => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];