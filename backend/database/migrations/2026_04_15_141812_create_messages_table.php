<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRACIÓ: create_messages_table
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix la taula del xat intern del joc. Emmagatzema els 
 *   missatges enviats entre amics, permetent la comunicació 
 *   en temps real o asíncrona.
 * ============================================================
 */

return new class extends Migration
{
    /**
     * Executa la migració per crear la taula 'messages'.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // L'usuari que envia el missatge.
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            // L'usuari que rep el missatge.
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            // Contingut de text del missatge.
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverteix la migració.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};