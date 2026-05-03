<?php

/**
 * ============================================================================
 * FITXER: app/Http/Controllers/AdminController.php
 * ============================================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest controlador centralitza totes les accions privilegiades destinades
 *   a l'administrador del sistema (usuari amb rol 'robot'). Està dissenyat per
 *   oferir dades i capacitats de modificació al panell de control d'Angular.
 * 
 * FUNCIONALITATS PRINCIPALS:
 *   - Llistar usuaris per a operacions de suport o regals.
 *   - Injecció manual d'ítems (xuxes/vacunes) amb càlcul de capacitat.
 *   - Lliurament de Xuxemons aleatoris per a esdeveniments o recompenses.
 *   - Ajust de la dificultat del joc mitjançant la gestió de probabilitats globals.
 *
 * SEGURETAT:
 *   L'accés a aquestes rutes ha de ser filtrat pel middleware de rol 'robot'
 *   a més de l'autenticació JWT.
 * ============================================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Xuxemon;
use App\Models\Setting;

class AdminController extends Controller
{
    /**
     * Obté una llista de tots els usuaris registrats al sistema.
     * 
     * Retorna dades bàsiques (id, nom i custom_id) per poder omplir els selectors
     * del frontend d'administració on es tria a quin jugador realitzar una acció.
     * 
     * @return \Illuminate\Http\JsonResponse Llista d'usuaris.
     */
    public function getUsers()
    {
        $users = User::select('id', 'name', 'custom_id')->get();
        return response()->json($users);
    }

    /**
     * Entrega directament un ítem a la motxilla d'un usuari.
     * 
     * Aquest mètode implementa la REGLA D'OCUPACIÓ DE LA MOTXILLA:
     * - El límit màxim és de 20 slots ocupats.
     * - Les Xuxes són apilables (stacks de 5 unitats ocupen 1 slot).
     * - Les Vacunes NO són apilables (cada unitat ocupa 1 slot propi).
     * 
     * @param Request $request Objecte amb user_id, dades de l'ítem i quantitat.
     * @return \Illuminate\Http\JsonResponse Missatge de confirmació o error per falta d'espai.
     */
    public function giveItem(Request $request)
    {
        // Validem que les dades d'entrada siguin coherents i l'usuari existeixi.
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'item_type' => 'required|in:xuxe,vacuna',
            'item_name' => 'required|string',
            'quantity'  => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($request->user_id);

        // Busquem o creem l'ítem al catàleg general.
        $item = Item::firstOrCreate(
            ['name' => $request->item_name, 'type' => $request->item_type],
            ['is_stackable' => $request->item_type === 'xuxe']
        );

        // CÀLCUL D'ESPAI A LA MOTXILLA:
        // Recorrem tot l'inventari actual de l'usuari per sumar els slots ocupats.
        $totalSlotsUsed = 0;
        foreach ($user->items as $userItem) {
            if ($userItem->is_stackable) {
                // Si és xuxe, cada bloc de 5 (complet o parcial) compta com 1 slot.
                $totalSlotsUsed += ceil($userItem->pivot->quantity / 5);
            } else {
                // Si és vacuna, cada unitat física ocupa un slot de la motxilla.
                $totalSlotsUsed += $userItem->pivot->quantity;
            }
        }

        // Verificació del límit de negoci (20 slots).
        if ($totalSlotsUsed >= 20) {
            return response()->json([
                'error' => 'La motxilla del jugador ja està plena (20/20 slots ocupats).'
            ], 400);
        }

        // Assignació de l'ítem al pivot 'user_items'.
        $existingItem = $user->items()->where('item_id', $item->id)->first();

        if ($existingItem) {
            // Si ja el tenia, simplement incrementem la quantitat global.
            $user->items()->updateExistingPivot($item->id, [
                'quantity' => $existingItem->pivot->quantity + $request->quantity
            ]);
        } else {
            // Si és la primera vegada que el rep, creem la fila al pivot.
            $user->items()->attach($item->id, ['quantity' => $request->quantity]);
        }

        return response()->json(['message' => 'Ítem entregat i registrat a la motxilla correctament.']);
    }

    /**
     * Regala un exemplar de Xuxemon aleatori a un usuari.
     * 
     * Útil per a recompenses especials des del panell d'administració.
     * No hi ha límit de col·lecció de Xuxemons definit per ara.
     * 
     * @param Request $request Conté el user_id del destinatari.
     * @return \Illuminate\Http\JsonResponse Nom del Xuxemon regalat.
     */
    public function giveRandomXuxemon(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // Selecció aleatòria des de la base de dades.
        $randomXuxemon = Xuxemon::inRandomOrder()->first();

        if (!$randomXuxemon) {
            return response()->json(['error' => 'No hi ha Xuxemons definits al catàleg del sistema.'], 404);
        }

        // Afegim la nova instància a l'usuari (food_eaten 0, disease null per defecte).
        $user->xuxemons()->attach($randomXuxemon->id);

        return response()->json([
            'message' => "L'usuari ha rebut un exemplar de l'espècie: " . $randomXuxemon->name
        ]);
    }

    /**
     * Obté els paràmetres de configuració globals del joc.
     * 
     * Retorna les probabilitats de malaltia que afecten a tots els jugadors.
     * 
     * @return \Illuminate\Http\JsonResponse Mapa clau-valor de configuracions.
     */
    public function getSettings()
    {
        $settings = Setting::pluck('value', 'key');
        return response()->json($settings);
    }

    /**
     * Actualitza les probabilitats globals de malaltia del sistema.
     * 
     * Aquesta és la eina principal de l'administrador per balancejar la dificultat.
     * 
     * @param Request $request Valors enters (0-100) per a cada tipus de malaltia.
     * @return \Illuminate\Http\JsonResponse Confirmació del canvi.
     */
    public function updateSettings(Request $request)
    {
        // Validem que els percentatges siguin valors reals de probabilitat.
        $validated = $request->validate([
            'atracon_prob'    => 'required|integer|min:0|max:100',
            'sobredosis_prob' => 'required|integer|min:0|max:100',
            'bajon_prob'      => 'required|integer|min:0|max:100',
        ]);

        // Guardem o actualitzem cada paràmetre a la taula 'settings'.
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'Paràmetres globals de dificultat actualitzats correctament.']);
    }
}