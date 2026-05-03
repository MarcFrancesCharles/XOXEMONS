<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_xuxemons_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix la taula del catàleg mestre d'espècies de Xuxemons. 
 *   Conté les plantilles (nom, tipus, mida) que s'utilitzaran 
 *   per crear els Xuxemons individuals dels usuaris.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'xuxemons'.
     */
    public function up() {
        Schema::create('xuxemons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Tipus elementals disponibles al joc.
            $table->enum('type', ['Aigua', 'Terra', 'Aire']);
            // Mida de la criatura (estats d'evolució).
            $table->enum('size', ['Petit', 'Mitja', 'Gran']);
            // Ruta de la imatge de l'espècie.
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverteix la migració.
     */
    public function down(): void
    {
        Schema::dropIfExists('xuxemons');
    }
};
