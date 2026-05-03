<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_user_items_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix la taula pivot de la motxilla del jugador. 
 *   Vincula usuaris amb ítems i en guarda la quantitat disponible. 
 *   Implementa la persistència de l'inventari.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'user_items'.
     */
    public function up() {
        Schema::create('user_items', function (Blueprint $table) {
            $table->id();
            // Usuari propietari de l'objecte.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Referència a l'objecte del catàleg.
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            // Quantitat d'aquest objecte que té l'usuari.
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverteix la migració.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_items');
    }
};
