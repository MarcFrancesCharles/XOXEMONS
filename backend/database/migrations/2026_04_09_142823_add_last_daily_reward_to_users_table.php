<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: add_last_daily_reward_to_users_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Afegeix el camp per controlar la darrera vegada que un 
 *   usuari ha reclamat la seva recompensa diària. 
 *   Permet implementar la lògica de 24 hores de bloqueig.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Afegeix la columna 'last_daily_reward' a la taula d'usuaris.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Guarda la data i hora exacta del darrer reclam.
            $table->timestamp('last_daily_reward')->nullable()->after('password');
        });
    }

    /**
     * Elimina la columna 'last_daily_reward'.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_daily_reward');
        });
    }
};