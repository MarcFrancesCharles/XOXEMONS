<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_friendships_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix la taula que gestiona les relacions socials entre 
 *   els jugadors. Permet enviar sol·licituds, acceptar-les i 
 *   mantenir una llista d'amics bidireccional.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'friendships'.
     */
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            // L'usuari que envia la sol·licitud.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // L'usuari que la rep.
            $table->foreignId('friend_id')->constrained('users')->onDelete('cascade');
            // Estat de la relació.
            $table->enum('status', ['pending', 'accepted'])->default('pending');
            $table->timestamps();

            // Clau única composta per evitar que un usuari enviï 
            // múltiples sol·licituds a la mateixa persona.
            $table->unique(['user_id', 'friend_id']);
        });
    }

    /**
     * Reverteix la migració.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};