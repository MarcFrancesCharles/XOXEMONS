<?php

/**
 * ============================================================
 * FITXER: app/Models/Message.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model que representa un missatge individual enviat a través 
 *   del xat privat entre dos amics.
 *
 * FUNCIONALITATS CLAU:
 *   - Emmagatzemar el contingut de text del missatge.
 *   - Identificar l'emissor i el receptor.
 *   - Mantenir la marca de temps (created_at) per a l'ordenació 
 *     cronològica de la conversa.
 *
 * MAPA DE CONNEXIONS:
 *   → Referencia a User (sender_id i receiver_id).
 *   → Usat pel ChatController per recuperar l'historial.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * Camps que es poden omplir massivament.
     * 
     * - sender_id: ID de l'usuari que envia el missatge.
     * - receiver_id: ID de l'usuari que rep el missatge.
     * - content: Text del missatge (limitat normalment a 1000 caràcters).
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
    ];

    /**
     * Relació amb l'usuari que ha enviat el missatge.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relació amb l'usuari que ha rebut el missatge.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}