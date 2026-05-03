<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: add_fields_to_user_xuxemons_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Afegeix els camps dinàmics a la relació entre l'usuari i 
 *   el seu Xuxemon. Aquests camps són essencials per a les 
 *   mecàniques de joc de la Fase 2 (alimentació i malalties).
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Afegeix les columnes de joc a la taula 'user_xuxemons'.
     */
    public function up(): void
    {
        Schema::table('user_xuxemons', function (Blueprint $table) {
            // Comptador de quantes xuxes ha menjat la criatura. 
            // Determina quan pot evolucionar.
            $table->integer('food_eaten')->default(0); 
            // Estat de salut: indica si té 'Atracón', 'Bajón de azúcar', etc.
            $table->string('disease')->nullable();     
        });
    }

    /**
     * Elimina les columnes afegides.
     */
    public function down(): void
    {
        Schema::table('user_xuxemons', function (Blueprint $table) {
            $table->dropColumn(['food_eaten', 'disease']);
        });
    }
};