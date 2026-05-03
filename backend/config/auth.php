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

    /*
    |--------------------------------------------------------------------------
    | Valors per defecte de l'Autenticació
    |--------------------------------------------------------------------------
    |
    | Aquesta opció defineix el "guard" i el gestor de contrasenyes per defecte.
    | Podeu canviar aquests valors segons les vostres necessitats, però per a
    | la majoria d'aplicacions aquests valors són suficients.
    |
    */

    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards d'Autenticació
    |--------------------------------------------------------------------------
    |
    | Aquí es defineixen tots els guards de la vostra aplicació. Un "guard"
    | defineix com es recuperen els usuaris per a cada petició.
    |
    | El guard 'api' utilitza el driver 'jwt' per validar tokens a les rutes de la API.
    |
    */

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver'   => 'jwt', // Driver proporcionat per tymon/jwt-auth
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveïdors d'Usuaris (Providers)
    |--------------------------------------------------------------------------
    |
    | Els proveïdors defineixen com es recuperen els usuaris de la base de dades.
    | En aquest projecte fem servir Eloquent amb el model 'User'.
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gestió de Restabliment de Contrasenyes
    |--------------------------------------------------------------------------
    |
    | Aquí es configura la lògica per restablir les contrasenyes, incloent-hi
    | la taula de tokens i el temps d'expiració.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temps d'Espera de Confirmació de Contrasenya
    |--------------------------------------------------------------------------
    |
    | Defineix el temps (en segons) abans que caduqui una confirmació de
    | contrasenya i se li demani a l'usuari que la torni a introduir.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];