<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/BattleController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Gestiona el sistema de batalles entre jugadors. Proveeix
 *   les dades necessàries per a la pantalla de batalla (Xuxemons
 *   sans de tots dos jugadors) i executa la transferència de
 *   propietat d'un Xuxemon del perdedor al guanyador.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\UserXuxemon (consulta i modificació del propietari)
 *   → Model: App\Models\Xuxemon (via JOIN per obtenir nom, tipus, mida, imatge)
 *   → Model: App\Models\User (identificar els jugadors involucrats)
 *   → Depèn de: FriendController (els jugadors han de ser amics per batallar)
 *   → Cridat des de: routes/api.php (rutes /battle/{friendId} i /battle/transfer)
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
     * Un Xuxemon amb malaltia no pot lluitar: és una regla de negoci del joc.
     *
     * @param int $friendId  ID del jugador rival (ha de ser amic)
     */
    public function getBattleData($friendId)
    {
        $myId = Auth::id();

        // Usem JOIN en lloc de with() perquè necessitem columnes de xuxemons
        // (nom, tipus, etc.) juntament amb l'id del pivot (per a la transferència).
        // whereNull('disease') filtra els Xuxemons sans: un malalt no pot combatre.
        $myXuxemons = UserXuxemon::where('user_id', $myId)
                                 ->whereNull('disease')
                                 ->join('xuxemons', 'user_xuxemons.xuxemon_id', '=', 'xuxemons.id')
                                 ->select(
                                     'user_xuxemons.id as pivot_id', // Angular usa pivot_id per a /battle/transfer
                                     'xuxemons.name',
                                     'xuxemons.type',
                                     'xuxemons.size',
                                     'xuxemons.image'
                                 )
                                 ->get();

        // Mateixa consulta per al rival. Angular rep els dos conjunts i
        // permet a cada jugador triar el seu combatent.
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
    // TRANSFERIR XUXEMON AL GUANYADOR
    // ─────────────────────────────────────────────────────────

    /**
     * Canvia el propietari d'un Xuxemon del perdedor al guanyador.
     * La batalla no té "motor" servidor: Angular determina el resultat.
     * El backend només executa la transferència amb validació de seguretat.
     */
    public function transferXuxemon(Request $request)
    {
        $request->validate([
            'winner_id'               => 'required|exists:users,id',
            'loser_xuxemon_pivot_id'  => 'required|exists:user_xuxemons,id'
        ]);

        // Recuperem la fila de user_xuxemons que representa el Xuxemon a transferir.
        $xuxemonTransfer = UserXuxemon::findOrFail($request->loser_xuxemon_pivot_id);
        $authedId        = Auth::id();

        // VALIDACIÓ DE SEGURETAT CRÍTICA:
        // Comprovem que l'usuari que fa la petició forma part de la batalla.
        // Sense aquesta validació, qualsevol jugador autenticat podria cridar
        // /battle/transfer des de Postman i robar Xuxemons sense lluitar.
        // L'usuari autenticat ha de ser o el guanyador (winner_id) o
        // el propietari actual del Xuxemon a transferir (el perdedor).
        if ($authedId != $request->winner_id && $authedId != $xuxemonTransfer->user_id) {
            return response()->json(['message' => 'Acció no autoritzada.'], 403);
        }

        // La transferència és tan simple com canviar el user_id del pivot.
        // El Xuxemon (amb el seu food_eaten i disease) passa tal qual al guanyador.
        $xuxemonTransfer->user_id = $request->winner_id;
        $xuxemonTransfer->save();

        return response()->json(['message' => 'El Xuxemon ha estat robat amb èxit!']);
    }
}