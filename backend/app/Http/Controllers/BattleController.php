<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/BattleController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest controlador gestiona el sistema de batalles entre 
 *   jugadors (PvP). Proveeix les dades dels combatents aptes 
 *   i formalitza el resultat final mitjançant la transferència
 *   de propietat del Xuxemon del perdedor al guanyador.
 *
 * FUNCIONALITATS PRINCIPALS:
 *   - Filtrar Xuxemons sans (aptes per lluitar).
 *   - Preparar el dataset per a la pantalla de combat d'Angular.
 *   - Executar el "robatori" de la criatura perdedora amb validació
 *     de seguretat per evitar trampes.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\UserXuxemon (consulta i canvi de propietari)
 *   → Relació: FriendController (els jugadors han de ser amics per batallar)
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserXuxemon;
use Illuminate\Support\Facades\Auth;

class BattleController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // OBTENIR DADES DE BATALLA
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna els Xuxemons aptes per al combat de tots dos jugadors.
     * 
     * Regla de negoci: Un Xuxemon amb qualsevol malaltia (disease != null)
     * no pot participar en una batalla. Està massa feble.
     *
     * @param int $friendId ID del jugador rival.
     */
    public function getBattleData($friendId)
    {
        $myId = Auth::id();

        // Busquem els meus Xuxemons sans.
        // Utilitzem un JOIN per obtenir els noms i imatges de l'espècie base
        // mantenint l'ID del pivot (pivot_id) que és el que usarem per a la transferència.
        $myXuxemons = UserXuxemon::where('user_id', $myId)
                                 ->whereNull('disease') // Només Xuxemons sans.
                                 ->join('xuxemons', 'user_xuxemons.xuxemon_id', '=', 'xuxemons.id')
                                 ->select(
                                     'user_xuxemons.id as pivot_id',
                                     'xuxemons.name',
                                     'xuxemons.type',
                                     'xuxemons.size',
                                     'xuxemons.image'
                                 )
                                 ->get();

        // Realitzem la mateixa consulta per al rival escollit.
        $friendXuxemons = UserXuxemon::where('user_id', $friendId)
                                     ->whereNull('disease')
                                     ->join('xuxemons', 'user_xuxemons.xuxemon_id', '=', 'xuxemons.id')
                                     ->select(
                                         'user_xuxemons.id as pivot_id',
                                         'xuxemons.name',
                                         'xuxemons.type',
                                         'xuxemons.size',
                                         'xuxemons.image'
                                     )
                                     ->get();

        return response()->json([
            'me'     => $myXuxemons,
            'friend' => $friendXuxemons
        ]);
    }


    // ─────────────────────────────────────────────────────────
    // TRANSFERIR XUXEMON AL GUANYADOR (Final de Batalla)
    // ─────────────────────────────────────────────────────────

    /**
     * Canvia el propietari d'un Xuxemon del perdedor al guanyador.
     * 
     * Nota: El "motor de combat" resideix al frontend d'Angular per 
     * oferir una experiència fluida i visual. Un cop decidit el resultat, 
     * el backend s'encarrega de fer el canvi permanent a la base de dades.
     */
    public function transferXuxemon(Request $request)
    {
        // Validem que els paràmetres enviats existeixin a la base de dades.
        $request->validate([
            'winner_id'               => 'required|exists:users,id',
            'loser_xuxemon_pivot_id'  => 'required|exists:user_xuxemons,id'
        ]);

        $xuxemonTransfer = UserXuxemon::findOrFail($request->loser_xuxemon_pivot_id);
        $authedId        = Auth::id();

        // VALIDACIÓ DE SEGURETAT CRÍTICA:
        // Evitem que un usuari malintencionat pugui cridar aquesta ruta via API (Postman)
        // per robar Xuxemons sense lluitar. L'usuari que fa la petició ha de ser o bé 
        // el guanyador o bé el propietari actual (el perdedor) del Xuxemon.
        if ($authedId != $request->winner_id && $authedId != $xuxemonTransfer->user_id) {
            return response()->json(['message' => 'Acció no autoritzada. No formes part d\'aquesta batalla.'], 403);
        }

        // Executem la transferència: el Xuxemon canvia de user_id.
        // Es mantenen el seu historial de menjar i qualsevol altre estat (excepte malaltia, 
        // ja que hem validat que estava sa en començar).
        $xuxemonTransfer->user_id = $request->winner_id;
        $xuxemonTransfer->save();

        return response()->json(['message' => 'Batalla finalitzada: El Xuxemon ha estat transferit amb èxit!']);
    }
}