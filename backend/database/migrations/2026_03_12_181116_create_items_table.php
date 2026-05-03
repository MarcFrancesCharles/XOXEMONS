<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_items_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix la taula del catàleg mestre d'objectes (ítems). 
 *   Conté les plantilles per a xuxes i vacunes que els jugadors 
 *   podran tenir a la seva motxilla.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'items'.
     */
    public function up() {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Categoria de l'objecte: alimentació o medicina.
            $table->enum('type', ['xuxe', 'vacuna']);
            // Indica si l'objecte es pot apilar en un sol slot de motxilla.
            $table->boolean('is_stackable'); 
            // Ruta de la imatge de l'objecte.
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverteix la migració.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
