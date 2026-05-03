<?php

/**
 * ============================================================
 * FITXER: routes/console.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest fitxer és on es defineixen les comandes de consola 
 *   basades en clausures (Closure-based commands). Cada comanda 
 *   està lligada a una instància de comanda que permet 
 *   interaccionar amb l'usuari a través del terminal (Artisan).
 * ============================================================
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Comanda d'exemple 'inspire': Mostra una frase inspiradora.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Mostra una frase inspiradora per al terminal');
