<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/ChatController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Gestiona el sistema de missatgeria privada entre jugadors.
 *   Implementa una capa de seguretat basada en l'amistat: només
 *   es poden enviar i llegir missatges entre jugadors que prèviament
 *   s'hagin acceptat com a amics.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\Message (creació i lectura de missatges)
 *   → Model: App\Models\Friendship (validació que dos usuaris son amics)
 *   → Depèn de: FriendController (la lògica d'amistat és prerequisit)
 *   → Cridat des de: routes/api.php (rutes GET i POST /chat/{friendId})
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // MÈTODE AUXILIAR: VERIFICACIÓ D'AMISTAT
    // ─────────────────────────────────────────────────────────

    /**
     * Comprova si dos usuaris tenen una relació d'amistat acceptada.
     * Mètode privat perquè és una utilitat interna d'aquest controlador.
     * Si el sistema creixés, es podria extreure a un Service o un Helper global.
     *
     * @param int $userId    ID de l'usuari A
     * @param int $friendId  ID de l'usuari B
     * @return bool
     */
    private function areFriends($userId, $friendId)
    {
        // Comprovem les dues direccions de la relació, ja que la taula friendships
        // emmagatzema qui va iniciar la sol·licitud (user_id) i qui la va rebre (friend_id),
        // i els papers poden estar invertits.
        return Friendship::where('status', 'accepted')
            ->where(function ($q) use ($userId, $friendId) {
                $q->where('user_id', $userId)->where('friend_id', $friendId);
            })->orWhere(function ($q) use ($userId, $friendId) {
                $q->where('user_id', $friendId)->where('friend_id', $userId);
            })->exists();
    }


    // ─────────────────────────────────────────────────────────
    // OBTENIR HISTORIAL DE MISSATGES
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna tots els missatges entre l'usuari autenticat i un amic,
     * ordenats cronològicament de més antic a més recent.
     *
     * @param int $friendId  ID de l'usuari amic
     */
    public function getMessages($friendId)
    {
        $myId = Auth::id();

        // Comprovació de seguretat: un jugador no pot llegir missatges
        // d'una conversa entre dos usuaris que no sigui ell.
        // Si no son amics, retornem 403 Forbidden (autenticat però no autoritzat).
        if (!$this->areFriends($myId, $friendId)) {
            return response()->json(['message' => 'No tens permís. No sou amics.'], 403);
        }

        // Busquem els missatges de la conversa en ambdues direccions:
        // missatges que jo he enviat a ell + missatges que ell m'ha enviat a mi.
        // orderBy('created_at', 'asc') per mostrar primer els més antics (estil xat).
        $messages = Message::where(function ($q) use ($myId, $friendId) {
                $q->where('sender_id', $myId)->where('receiver_id', $friendId);
            })->orWhere(function ($q) use ($myId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }


    // ─────────────────────────────────────────────────────────
    // ENVIAR UN MISSATGE
    // ─────────────────────────────────────────────────────────

    /**
     * Crea un nou missatge de l'usuari autenticat cap a un amic.
     *
     * @param int $friendId  ID de l'usuari destinatari
     */
    public function sendMessage(Request $request, $friendId)
    {
        // Limitem el contingut a 1000 caràcters per evitar spam o sobrecàrrega de la BD.
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $myId = Auth::id();

        // Mateixa validació d'amistat: no es pot enviar un missatge a algú que no és amic.
        // Doble capa de seguretat: el frontend ja ho hauria de bloquejar, però el backend
        // ha de ser la font de veritat.
        if (!$this->areFriends($myId, $friendId)) {
            return response()->json([
                'message' => 'No pots enviar missatges a usuaris que no són amics teus.'
            ], 403);
        }

        // Creem el missatge a la BD. created_at es desa automàticament per Eloquent
        // i servirà per ordenar l'historial cronològicament.
        $message = Message::create([
            'sender_id'   => $myId,
            'receiver_id' => $friendId,
            'content'     => $request->input('content')
        ]);

        return response()->json([
            'message' => 'Missatge enviat',
            'data'    => $message
        ]);
    }
}