<?php

/**
 * ============================================================
 * FITXER: app/Models/Friendship.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model que gestiona la taula de relacions socials entre usuaris. 
 *   Defineix el vincle de "amistat", el qual pot estar en estat 
 *   pendent o acceptat.
 *
 * FUNCIONALITATS CLAU:
 *   - Vincular dos perfils d'usuari (sol·licitant i receptor).
 *   - Controlar l'estat de la relació (pending/accepted).
 *   - Servir de base per als permisos de Xat i Batalles.
 *
 * MAPA DE CONNEXIONS:
 *   → Referencia a User (user_id i friend_id).
 *   → Usat extensivament pel FriendController.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    /**
     * Camps habilitats per a l'assignació massiva.
     * 
     * - user_id: ID de l'usuari que inicia la sol·licitud d'amistat.
     * - friend_id: ID de l'usuari que rep la sol·licitud.
     * - status: Estat de l'amistat (normalment 'pending' o 'accepted').
     */
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    /**
     * Relació amb l'usuari que ha enviat la sol·licitud.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relació amb l'usuari que ha rebut la sol·licitud.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
}