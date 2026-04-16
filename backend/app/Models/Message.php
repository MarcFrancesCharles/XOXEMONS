<?php

/**
 * ============================================================
 * FITXER: app/Models/Message.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model que representa un missatge de text entre dos jugadors
 *   amics. Cada fila és un missatge unidireccional (sender → receiver).
 *   L'historial de conversa es reconstrueix a ChatController agrupant
 *   els missatges en ambdues direccions.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: messages (migració: 2026_04_15_141812_create_messages_table.php)
 *   → Usat per: ChatController (getMessages, sendMessage)
 *   → Prerequisit: Friendship (s'ha de tenir una amistat acceptada)
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * Camps assignables massivament.
     *
     * Columnes:
     *   - sender_id    (FK → users.id: qui envia el missatge)
     *   - receiver_id  (FK → users.id: qui el rep)
     *   - content      (text: el missatge en si, màx 1000 caràcters per validació al controlador)
     *
     * created_at es gestiona automàticament per Eloquent i s'usa per ordenar
     * l'historial cronològicament a ChatController.
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content'
    ];
}