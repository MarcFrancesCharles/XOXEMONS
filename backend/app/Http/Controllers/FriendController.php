<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/FriendController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Gestiona tot el sistema d'amistats entre jugadors: cerca,
 *   enviament i recepció de sol·licituds, acceptació/rebuig,
 *   llistat d'amics i eliminació d'amistats.
 *   Actua com a prerequisit per al Xat i les Batalles,
 *   que requereixen que dos jugadors siguin amics.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (cercar i obtenir dades d'usuaris)
 *   → Model: App\Models\Friendship (gestionar la taula d'amistats)
 *   → Cridat des de: routes/api.php (rutes /friends/*)
 *   → Depèn de: ChatController i BattleController (usen areFriends internament)
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
     * Cerca usuaris pel seu custom_id (format Nom#XXXX).
     * S'usa al panell d'Angular per trobar jugadors als qui enviar
     * una sol·licitud d'amistat.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->query('q');
        $myId  = Auth::id();

        // Busquem per LIKE per permetre cerques parcials (p.ex. "Jan" troba "Jan#1042").
        // Excloem l'usuari actual perquè no té sentit afegir-se a si mateix.
        $users = User::where('custom_id', 'LIKE', "%{$query}%")
                     ->where('id', '!=', $myId)
                     ->select('id', 'name', 'custom_id')
                     ->get();

        return response()->json($users);
    }


    // ─────────────────────────────────────────────────────────
    // ENVIAR SOL·LICITUD D'AMISTAT
    // ─────────────────────────────────────────────────────────

    /**
     * Crea una sol·licitud d'amistat pendent entre l'usuari autenticat
     * i el jugador destinatari.
     */
    public function sendRequest(Request $request)
    {
        $request->validate(['friend_id' => 'required|exists:users,id']);

        $userId   = Auth::id();
        $friendId = $request->friend_id;

        // Evitem auto-sol·licituds: un usuari no es pot afegir a si mateix.
        // Validació de negoci que el camp 'exists:users,id' no cobreix.
        if ($userId === $friendId) {
            return response()->json(['message' => 'No et pots afegir a tu mateix!'], 400);
        }

        // Comprovem les dues direccions (A→B i B→A) per evitar duplicats.
        // La taula friendship_table té un unique(['user_id','friend_id']) però
        // no cobreix la inversa, per tant cal comprovar-ho manualment.
        $existing = Friendship::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $userId);
        })->first();

        // Si ja existeix una relació (pendent o acceptada), no en creem una altra.
        if ($existing) {
            return response()->json([
                'message' => 'Ja existeix una sol·licitud o amistat amb aquest usuari.'
            ], 400);
        }

        // Creem la sol·licitud en estat 'pending'. L'altre usuari la veurà
        // a /friends/requests i podrà acceptar-la o rebutjar-la.
        Friendship::create([
            'user_id'   => $userId,
            'friend_id' => $friendId,
            'status'    => 'pending'
        ]);

        return response()->json(['message' => 'Sol·licitud enviada correctament!']);
    }


    // ─────────────────────────────────────────────────────────
    // LLISTAR SOL·LICITUDS REBUDES PENDENTS
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna les sol·licituds d'amistat que altres jugadors han enviat
     * a l'usuari autenticat i que estan pendents d'aprovació.
     */
    public function getPendingRequests()
    {
        // Filtrem per friend_id = yo i status = 'pending'.
        // Fem JOIN amb users per obtenir el nom i custom_id del sol·licitant,
        // ja que el Frontend necessita mostrar qui envia la sol·licitud.
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


    // ─────────────────────────────────────────────────────────
    // ACCEPTAR SOL·LICITUD
    // ─────────────────────────────────────────────────────────

    /**
     * Canvia l'estat d'una sol·licitud de 'pending' a 'accepted'.
     * A partir d'aquest moment, tots dos usuaris poden xatejar i batallar.
     *
     * @param int $id  ID de la fila a friendships
     */
    public function acceptRequest($id)
    {
        // La validació amb friend_id = Auth::id() és crítica per seguretat:
        // un usuari NO pot acceptar sol·licituds que no van adreçades a ell.
        // firstOrFail() llança un 404 si no es troba la fila, impedint acceptar
        // sol·licituds alienes.
        $friendship = Friendship::where('id', $id)
                                ->where('friend_id', Auth::id())
                                ->firstOrFail();

        $friendship->status = 'accepted';
        $friendship->save();

        return response()->json(['message' => 'Sol·licitud acceptada! Ja sou amics.']);
    }


    // ─────────────────────────────────────────────────────────
    // REBUTJAR SOL·LICITUD
    // ─────────────────────────────────────────────────────────

    /**
     * Elimina una sol·licitud d'amistat pendent (rebuig o cancel·lació).
     * Usem delete() en lloc de canviar l'estat per mantenir la taula neta.
     *
     * @param int $id  ID de la fila a friendships
     */
    public function rejectRequest($id)
    {
        // Igual que acceptRequest: validem que la sol·licitud va adreçada a mi.
        $friendship = Friendship::where('id', $id)
                                ->where('friend_id', Auth::id())
                                ->firstOrFail();

        // Esborrem la fila completament per permetre futures sol·licituds
        // del mateix usuari (si no esborrarem, la unique constraint ho impediria).
        $friendship->delete();

        return response()->json(['message' => 'Sol·licitud rebutjada.']);
    }


    // ─────────────────────────────────────────────────────────
    // LLISTAR AMICS
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna la llista d'amics acceptats de l'usuari autenticat.
     * La relació és bidireccional: l'usuari pot ser tant user_id com friend_id.
     */
    public function getFriends()
    {
        $myId = Auth::id();

        // Busquem totes les amistats acceptades on jo aparec en qualsevol direcció.
        $friends = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($myId) {
                $q->where('user_id', $myId)->orWhere('friend_id', $myId);
            })
            ->get()
            ->map(function ($friendship) use ($myId) {
                // Per a cada amistat, determinam qui és l'ALTRE usuari (no jo).
                // Si jo soc user_id, l'amic és friend_id, i viceversa.
                $otherUserId = ($friendship->user_id === $myId)
                    ? $friendship->friend_id
                    : $friendship->user_id;

                $friendData = User::find($otherUserId);

                // Retornem friendship_id perquè Angular el necessita per cridar
                // /friends/{id} (DELETE) i eliminar l'amistat.
                return [
                    'friendship_id' => $friendship->id,
                    'user_id'       => $friendData->id,
                    'name'          => $friendData->name,
                    'custom_id'     => $friendData->custom_id
                ];
            });

        return response()->json($friends);
    }


    // ─────────────────────────────────────────────────────────
    // ELIMINAR AMIC
    // ─────────────────────────────────────────────────────────

    /**
     * Elimina una amistat acceptada. Qualsevol dels dos amics pot fer-ho.
     *
     * @param int $id  ID de la fila a friendships
     */
    public function removeFriend($id)
    {
        $myId = Auth::id();

        // Permetre que QUALSEVOL dels dos amics elimini la relació
        // (no només qui la va iniciar). Per això comprovem les dues direccions.
        $friendship = Friendship::where('id', $id)
            ->where(function ($q) use ($myId) {
                $q->where('user_id', $myId)->orWhere('friend_id', $myId);
            })->firstOrFail();

        $friendship->delete();

        return response()->json(['message' => 'Amic eliminat correctament.']);
    }
}