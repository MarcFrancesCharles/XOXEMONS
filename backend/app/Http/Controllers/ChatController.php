<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/ChatController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest controlador gestiona la missatgeria privada en temps 
 *   real entre jugadors. Implementa una capa de seguretat basada 
 *   en el consentiment mutu: només els amics acceptats poden 
 *   intercanviar missatges.
 *
 * FUNCIONALITATS CLAU:
 *   - Recuperar l'historial de xat filtrat per conversa.
 *   - Enviar missatges amb validació d'amistat i integritat de contingut.
 *   - Garantir la privacitat: cap usuari pot llegir missatges de converses 
 *     on no estigui involucrat.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\Message (persistència dels missatges)
 *   → Model: App\Models\Friendship (verificació del vincle social)
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
     * Comprova si dos usuaris tenen una relació d'amistat formalment acceptada.
     * 
     * Aquesta utilitat interna és la base de la seguretat del xat. Si no hi ha
     * un registre 'accepted' a la taula friendships, l'intercanvi de dades 
     * es bloqueja immediatament.
     *
     * @param int $userId ID de l'usuari autenticat.
     * @param int $friendId ID del possible amic.
     * @return bool Cert si la relació existeix i està acceptada.
     */
    private function areFriends($userId, $friendId)
    {
        // La taula friendships pot tenir la relació en qualsevol sentit (A→B o B→A).
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
     * Recupera tots els missatges d'una conversa específica.
     * 
     * Ordena els missatges cronològicament (ascendent) per a que Angular 
     * els pugui mostrar en format bústia de xat.
     *
     * @param int $friendId ID de l'amic amb qui es manté la conversa.
     */
    public function getMessages($friendId)
    {
        $myId = Auth::id();

        // VALIDACIÓ DE SEGURETAT: No pots llegir missatges d'algú que no és el teu amic.
        if (!$this->areFriends($myId, $friendId)) {
            return response()->json(['message' => 'Acció prohibida: Només pots xatejar amb els teus amics.'], 403);
        }

        // Busquem els missatges en ambdues direccions (enviats i rebuts).
        $messages = Message::where(function ($q) use ($myId, $friendId) {
                $q->where('sender_id', $myId)->where('receiver_id', $friendId);
            })->orWhere(function ($q) use ($myId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc') // Els missatges nous apareixen al final.
            ->get();

        return response()->json($messages);
    }


    // ─────────────────────────────────────────────────────────
    // ENVIAR UN MISSATGE
    // ─────────────────────────────────────────────────────────

    /**
     * Envia un nou missatge a un amic.
     * 
     * Valida la longitud del text i confirma que el destinatari és un amic 
     * actiu abans de persistir el missatge.
     */
    public function sendMessage(Request $request, $friendId)
    {
        // Limitem el contingut per evitar abusos o càrregues excessives a la BD.
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $myId = Auth::id();

        // VALIDACIÓ DE SEGURETAT: Doble check per evitar injeccions des de la API.
        if (!$this->areFriends($myId, $friendId)) {
            return response()->json([
                'message' => 'No tens permís per enviar missatges a aquest usuari (no sou amics).'
            ], 403);
        }

        // Creació del registre de missatge. Eloquent gestionarà els timestamps automàticament.
        $message = Message::create([
            'sender_id'   => $myId,
            'receiver_id' => $friendId,
            'content'     => $request->input('content')
        ]);

        return response()->json([
            'message' => 'Missatge enviat correctament.',
            'data'    => $message
        ]);
    }
}