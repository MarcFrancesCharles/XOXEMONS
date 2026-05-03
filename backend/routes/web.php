<?php

/**
 * ============================================================
 * FITXER: routes/web.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest fitxer defineix les rutes que es carreguen a través 
 *   del middleware "web". En aquest projecte (XOXEMONS), 
 *   la interfície d'usuari principal és una SPA d'Angular externa. 
 *   Per tant, aquest fitxer només gestiona la pàgina de benvinguda 
 *   base del servidor.
 * ============================================================
 */

use Illuminate\Support\Facades\Route;

// Ruta arrel: Retorna la vista 'welcome.blade.php'.
// Útil per verificar que el servidor Laravel està funcionant.
Route::get('/', function () {
    return view('welcome');
});
