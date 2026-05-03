<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/FriendController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest controlador gestiona la xarxa social dels jugadors: 
 *   la cerca, la gestió de sol·licituds i el manteniment de 
 *   la llista d'amics.
 *
 * FUNCIONALITATS CLAU:
 *   - Cercar usuaris per identificador únic (Nom#XXXX).
 *   - Enviar, rebre, acceptar o rebutjar sol·licituds d'amistat.
 *   - Llistar amics acceptats per permetre xats i batalles.
 *   - Eliminar relacions d'amistat existents.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (identificació de perfils)
 *   → Model: App\Models\Friendship (gestió de la taula de relacions)
 *   → Prerequisit per a: ChatController i BattleController.
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // CERCA D'USUARIS
    // ─────────────────────────────────────────────────────────

    /**
     * Cerca jugadors pel seu custom_id (format Nom#XXXX).
     * 
     * S'utilitza al cercador del panell d'amics d'Angular per localitzar
     * nous jugadors a qui enviar una sol·licitud.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->query('q');
        $myId  = Auth::id();

        // Busquem per coincidència parcial (LIKE) i excloem l'usuari actual 
        // de la cerca (no es pot ser amic d'un mateix).
        $users = User::where('custom_id', 'LIKE', "%{$query}%")
                     ->where('id', '!=', $myId)
                     ->select('id', 'name', 'custom_id')
                     ->get();

        return response()->json($users);
    }


    // ─────────────────────────────────────────────────────────
    // GESTIÓ DE SOL·LICITUDS
    // ─────────────────────────────────────────────────────────

    /**
     * Envia una sol·licitud d'amistat a un altre jugador.
     * 
     * Comprova si ja existeix una relació prèvia (pendent o acceptada)
     * en qualsevol de les dues direccions (A→B o B→A) per evitar duplicats.
     */
    public function sendRequest(Request $request)
    {
        $request->validate(['friend_id' => 'required|exists:users,id']);

        $userId   = Auth::id();
        $friendId = $request->friend_id;

        // Validació: No es pot enviar una sol·licitud a un mateix.
        if ($userId === $friendId) {
            return response()->json(['message' => 'No et pots enviar una sol·licitud a tu mateix!'], 400);
        }

        // Verificació d'existència: Comprovem si ja hi ha un vincle registrat.
        $existing = Friendship::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $userId);
        })->first();

        if ($existing) {
            return response()->json([
                'message' => 'Ja hi ha una sol·licitud pendent o una amistat activa amb aquest usuari.'
            ], 400);
        }

        // Creem el registre en estat 'pending'.
        Friendship::create([
            'user_id'   => $userId,
            'friend_id' => $friendId,
            'status'    => 'pending'
        ]);

        return response()->json(['message' => 'Sol·licitud d\'amistat enviada amb èxit!']);
    }

    /**
     * Obté les sol·licituds que altres jugadors han enviat a l'usuari actual.
     * 
     * Serveix per omplir la safata d'entrada de sol·licituds a la UI d'Angular.
     */
    public function getPendingRequests()
    {
        // Busquem registres on l'usuari és el 'friend_id' (el receptor) i estan pendents.
        $requests = Friendship::where('friend_id', Auth::id())
                               ->where('status', 'pending')
                               ->join('users', 'friendships.user_id', '=', 'users.id')
                               ->select(
                                   'friendships.id as friendship_id',
                                   'users.id as user_id',
                                   'users.name',
                                   'users.custom_id'
                               )
                               ->get();

        return response()->json($requests);
    }

    /**
     * Accepta una sol·licitud d'amistat.
     * 
     * @param int $id ID del registre a la taula friendships.
     */
    public function acceptRequest($id)
    {
        // Seguretat: L'usuari només pot acceptar sol·licituds adreçades a ell.
        $friendship = Friendship::where('id', $id)
                                ->where('friend_id', Auth::id())
                                ->firstOrFail();

        $friendship->status = 'accepted';
        $friendship->save();

        return response()->json(['message' => 'Sol·licitud acceptada! Ja podeu xatejar i batallar.']);
    }

    /**
     * Rebutja o cancel·la una sol·licitud d'amistat pendent.
     * 
     * Elimina el registre de la base de dades per netejar l'historial.
     */
    public function rejectRequest($id)
    {
        // Seguretat: L'usuari només pot rebutjar sol·licituds que hagi rebut.
        $friendship = Friendship::where('id', $id)
                                ->where('friend_id', Auth::id())
                                ->firstOrFail();

        $friendship->delete();

        return response()->json(['message' => 'Sol·licitud rebutjada correctament.']);
    }


    // ─────────────────────────────────────────────────────────
    // LLISTAT D'AMICS
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna la llista completa d'amics acceptats del jugador.
     * 
     * Aquesta funció gestiona la BIDIRECCIONALITAT: el jugador pot aparèixer 
     * tant com a 'user_id' (qui va iniciar la petició) o com a 'friend_id' 
     * (qui la va rebre).
     */
    public function getFriends()
    {
        $myId = Auth::id();

        $friends = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($myId) {
                $q->where('user_id', $myId)->orWhere('friend_id', $myId);
            })
            ->get()
            ->map(function ($friendship) use ($myId) {
                // Identifiquem qui és l'amic en funció de quina columna ocupem nosaltres.
                $otherUserId = ($friendship->user_id === $myId)
                    ? $friendship->friend_id
                    : $friendship->user_id;

                $friendData = User::find($otherUserId);

                return [
                    'friendship_id' => $friendship->id,
                    'user_id'       => $friendData->id,
                    'name'          => $friendData->name,
                    'custom_id'     => $friendData->custom_id
                ];
            });

        return response()->json($friends);
    }

    /**
     * Elimina definitivament una amistat acceptada.
     * 
     * Qualsevol dels dos amics té el poder de trencar el vincle en qualsevol moment.
     */
    public function removeFriend($id)
    {
        $myId = Auth::id();

        // Busquem la fila on el jugador forma part del vincle d'amistat.
        $friendship = Friendship::where('id', $id)
            ->where(function ($q) use ($myId) {
                $q->where('user_id', $myId)->orWhere('friend_id', $myId);
            })->firstOrFail();

        $friendship->delete();

        return response()->json(['message' => 'Amic eliminat de la teva llista.']);
    }
}