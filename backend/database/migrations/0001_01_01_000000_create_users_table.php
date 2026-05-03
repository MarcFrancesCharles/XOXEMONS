<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_users_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix l'estructura de la taula principal d'usuaris. 
 *   Conté les dades d'autenticació, perfil i rols del sistema.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'users'.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Identificador personalitzat únic (ex: Nom#1234) utilitzat per al login.
            $table->string('custom_id')->unique(); 
            $table->string('name');
            $table->string('surnames');
            $table->string('email')->unique();
            $table->string('password');
            // Rol de l'usuari: 'usuari' (jugador) o 'robot' (administrador).
            $table->string('role')->default('usuari'); 
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverteix la migració (elimina la taula).
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
