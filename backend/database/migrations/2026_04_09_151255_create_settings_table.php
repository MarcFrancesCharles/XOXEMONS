<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_settings_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Crea la taula de configuracions globals. S'utilitza 
 *   per emmagatzemar variables del joc que l'administrador 
 *   pot ajustar dinàmicament (com les probabilitats de malaltia).
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'settings'.
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Identificador de la configuració (ex: 'atracon_prob').
            $table->string('key')->unique(); 
            // Valor numèric de la configuració.
            $table->integer('value');        
            $table->timestamps();
        });
    }

    /**
     * Reverteix la migració.
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};