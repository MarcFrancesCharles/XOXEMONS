<?php

namespace App\Providers;

/**
 * ============================================================
 * PROVEÏDOR: AppServiceProvider
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest és el lloc central per registrar qualsevol servei 
 *   de l'aplicació o realitzar tasques de "bootstrapping" 
 *   (arrencada). Aquí es poden definir bindings al contenidor, 
 *   configurar macros de rutes o ajustar paràmetres globals 
 *   del framework.
 * ============================================================
 */

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra qualsevol servei de l'aplicació.
     * S'executa abans que el mètode boot.
     */
    public function register(): void
    {
        //
    }

    /**
     * Executa accions després que tots els serveis hagin estat registrats.
     * Ideal per a configuracions globals que depenen d'altres serveis.
     */
    public function boot(): void
    {
        //
    }
}
