<?php

/**
 * ============================================================
 * FITXER: config/app.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Conté la configuració bàsica de l'aplicació: nom, entorn, 
 *   URL, idioma i zona horària. És l'arxiu central que defineix 
 *   com es comporta Laravel a nivell global.
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Nom de l'Aplicació
    |--------------------------------------------------------------------------
    |
    | Aquest valor és el nom de l'aplicació. S'utilitza quan el framework 
    | necessita mostrar el nom en notificacions o altres elements de la UI.
    |
    */

    'name' => env('APP_NAME', 'XOXEMONS'),

    /*
    |--------------------------------------------------------------------------
    | Entorn de l'Aplicació
    |--------------------------------------------------------------------------
    |
    | Aquest valor determina l'"entorn" en què s'està executant l'aplicació. 
    | Pot influir en com es configuren els diversos serveis.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Mode Depuració (Debug)
    |--------------------------------------------------------------------------
    |
    | Quan l'aplicació està en mode depuració, es mostraran missatges d'error 
    | detallats amb traces de pila. Si està desactivat, es mostra una pàgina genèrica.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de l'Aplicació
    |--------------------------------------------------------------------------
    |
    | Aquesta URL s'utilitza per generar URLs correctament quan es fa servir 
    | l'eina de línia de comandes Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona Horària
    |--------------------------------------------------------------------------
    |
    | Aquí podeu especificar la zona horària per defecte per a l'aplicació. 
    | Laravel la fa servir per a les funcions de data i hora de PHP.
    |
    */

    'timezone' => 'Europe/Madrid',

    /*
    |--------------------------------------------------------------------------
    | Configuració de l'Idioma (Locale)
    |--------------------------------------------------------------------------
    |
    | L'idioma determina la localització per defecte que s'utilitzarà 
    | per als mètodes de traducció de Laravel.
    |
    */

    'locale' => env('APP_LOCALE', 'ca'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'ca_ES'),

    /*
    |--------------------------------------------------------------------------
    | Clau d'Encriptació
    |--------------------------------------------------------------------------
    |
    | Aquesta clau és utilitzada pels serveis d'encriptació de Laravel i 
    | hauria de ser una cadena aleatòria de 32 caràcters.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver de Mode Manteniment
    |--------------------------------------------------------------------------
    |
    | Aquestes opcions determinen el driver utilitzat per gestionar 
    | l'estat de "mode manteniment" de l'aplicació.
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
