<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_user_xuxemons_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix la taula pivot que vincula els usuaris amb els 
 *   seus Xuxemons concrets. Representa la col·lecció o 
 *   inventari de criatures de cada jugador.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'user_xuxemons'.
     */
    public function up() {
        Schema::create('user_xuxemons', function (Blueprint $table) {
            $table->id();
            // Vinculació amb l'usuari propietari.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Vinculació amb l'espècie de Xuxemon del catàleg.
            $table->foreignId('xuxemon_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
    
    /**
     * Reverteix la migració.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_xuxemons');
    }
};
