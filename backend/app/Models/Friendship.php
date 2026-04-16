<?php

/**
 * ============================================================
 * FITXER: app/Models/Friendship.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model que representa la relació d'amistat entre dos usuaris.
 *   Gestiona el cicle de vida complet: sol·licitud pendent → acceptada.
 *   És la porta d'entrada al Xat i a les Batalles: sense una amistat
 *   acceptada, cap d'aquests dos sistemes no funciona.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: friendships (migració: 2026_04_14_154832_create_friendships_table.php)
 *   → Usat per: FriendController (totes les operacions d'amistat)
 *   → Usat per: ChatController (validació areFriends)
 *   → Referenciat per: BattleController (prerequisit d'amistat)
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    /**
     * Camps assignables massivament.
     *
     * Columnes:
     *   - user_id    (FK → users.id: qui envia la sol·licitud)
     *   - friend_id  (FK → users.id: qui la rep)
     *   - status     (enum: 'pending' | 'accepted')
     *
     * Nota: la taula té una restricció unique(['user_id', 'friend_id'])
     * que evita sol·licituds duplicades en la mateixa direcció.
     * FriendController comprova ambdues direccions per evitar duplicats inversos.
     */
    protected $fillable = [
        'user_id',
        'friend_id',
        'status'
    ];
}