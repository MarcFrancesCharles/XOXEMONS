<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserXuxemon;
use Illuminate\Support\Facades\Auth;

class BattleController extends Controller
{
    // 1. Obtenir les dades per a la pantalla de Batalla
    public function getBattleData($friendId) {
        $myId = Auth::id();

        // Recuperem els Xuxemons SANS del jugador
        $myXuxemons = UserXuxemon::where('user_id', $myId)
                                 ->whereNull('disease')
                                 ->join('xuxemons', 'user_xuxemons.xuxemon_id', '=', 'xuxemons.id')
                                 ->select('user_xuxemons.id as pivot_id', 'xuxemons.name', 'xuxemons.type', 'xuxemons.size', 'xuxemons.image')
                                 ->get();

        // Recuperem els Xuxemons SANS de l'amic
        $friendXuxemons = UserXuxemon::where('user_id', $friendId)
                                     ->whereNull('disease')
                                     ->join('xuxemons', 'user_xuxemons.xuxemon_id', '=', 'xuxemons.id')
                                     ->select('user_xuxemons.id as pivot_id', 'xuxemons.name', 'xuxemons.type', 'xuxemons.size', 'xuxemons.image')
                                     ->get();

        return response()->json([
            'me' => $myXuxemons,
            'friend' => $friendXuxemons
        ]);
    }

    // 2. Transferir el Xuxemon al guanyador
    public function transferXuxemon(Request $request) {
        $request->validate([
            'winner_id' => 'required|exists:users,id',
            'loser_xuxemon_pivot_id' => 'required|exists:user_xuxemons,id'
        ]);

        $xuxemonTransfer = UserXuxemon::findOrFail($request->loser_xuxemon_pivot_id);
        $authedId = Auth::id();

        // VALIDACIÓ DE SEGURETAT (Evita hackejos fets des de Postman directament)
        // L'usuari autenticat HA DE SER o el guanyador o el perdedor actual del Xuxemon
        if ($authedId != $request->winner_id && $authedId != $xuxemonTransfer->user_id) {
            return response()->json(['message' => 'Acció no autoritzada.'], 403);
        }

        // Canviem el propietari
        $xuxemonTransfer->user_id = $request->winner_id;
        $xuxemonTransfer->save();

        return response()->json(['message' => 'El Xuxemon ha estat robat amb èxit!']);
    }
}