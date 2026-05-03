<?php

/**
 * ============================================================
 * FITXER: config/filesystems.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configura els sistemes d'emmagatzematge de fitxers. En el 
 *   projecte XOXEMONS, és vital per gestionar les imatges de 
 *   les criatures i altres recursos estàtics.
 *
 * CONFIGURACIÓ:
 *   - El disc 'public' s'utilitza per a fitxers accessibles 
 *     des d'Angular mitjançant una URL pública.
 *   - S'utilitzen enllaços simbòlics per connectar la carpeta 
 *     storage amb la carpeta public del servidor web.
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Disc de Sistema de Fitxers per Defecte
    |--------------------------------------------------------------------------
    |
    | Aquí podeu especificar el disc que s'utilitzarà per defecte pel framework.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Discs del Sistema de Fitxers
    |--------------------------------------------------------------------------
    |
    | Aquí podeu configurar tants discs com vulgueu. Cada disc utilitza 
    | un "driver" específic (local, s3, etc.).
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enllaços Simbòlics
    |--------------------------------------------------------------------------
    |
    | Aquí es configuren els enllaços simbòlics que es crearan en executar 
    | l'ordre d'Artisan `storage:link`.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
