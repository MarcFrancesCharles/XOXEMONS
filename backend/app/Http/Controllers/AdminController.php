<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/AdminController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Proporciona les eines de gestió del joc per a l'usuari
 *   administrador (rol 'robot'). Permet donar ítems i Xuxemons
 *   als jugadors des del panell d'admin d'Angular, i gestionar
 *   la configuració global de probabilitats de malalties.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (cercar jugadors i modificar-ne la motxilla)
 *   → Model: App\Models\Item (crear o trobar ítems, associar-los a usuaris)
 *   → Model: App\Models\Xuxemon (seleccionar un Xuxemon aleatori)
 *   → Model: App\Models\Setting (llegir i escriure configuració global)
 *   → Taula pivot: user_items (via relació $user->items())
 *   → Taula pivot: user_xuxemons (via relació $user->xuxemons())
 *   → Cridat des de: routes/api.php (rutes /admin/*)
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Xuxemon;
use App\Models\Setting;

class AdminController extends Controller
{
    // 1. Retornem els usuaris per omplir el <select>
    public function getUsers()
    {
        // Retornem només els camps necessaris
        return response()->json(User::select('id', 'name', 'custom_id')->get());
    }

    // 2. Lògica per donar l'objecte al jugador (AMB FRE DE 20 ESPAIS)
    public function giveItem(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'item_type' => 'required|in:xuxe,vacuna',
            'item_name' => 'required|string',
            'quantity'  => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($request->user_id);

        // firstOrCreate és clau aquí: si l'ítem ja existeix a la taula 'items'
        // el recupera, i si no existeix el crea. Això permet que l'admin pugui
        // crear nous tipus d'ítems des del panell sense modificar la BD directament.
        $item = Item::firstOrCreate(
            ['name' => $request->item_name, 'type' => $request->item_type],
            // is_stackable depèn del tipus: les xuxes s'apilen, les vacunes no.
            ['is_stackable' => $request->item_type === 'xuxe']
        );

        // Calculem quants espais de motxilla ocupa ACTUALMENT l'inventari de l'usuari.
        // Iterem sobre tots els seus ítems per acumular el total d'espais usats.
        $totalSlotsUsed = 0;
        foreach ($user->items as $userItem) {
            if ($userItem->is_stackable) {
                // Les xuxes s'apilen de 5 en 5 per espai.
                // ceil() arrodoneix cap amunt: 6 xuxes = 2 espais (no 1.2).
                $totalSlotsUsed += ceil($userItem->pivot->quantity / 5);
            } else {
                // Cada vacuna ocupa 1 espai, independentment de la quantitat.
                $totalSlotsUsed += $userItem->pivot->quantity;
            }
        }

        // Si la motxilla ja és plena (20 espais), bloquejem l'operació.
        // Retornem 400 perquè és un error de negoci (no un error del servidor).
        if ($totalSlotsUsed >= 20) {
            return response()->json([
                'error' => 'La motxilla daquest jugador està plena (20/20 espais). Els objectes han estat descartats.'
            ], 400);
        }

        // Comprovem si l'usuari ja té aquest objecte a la seva motxilla
        $existingItem = $user->items()->where('item_id', $item->id)->first();

        if ($existingItem) {
            $user->items()->updateExistingPivot($item->id, [
                'quantity' => $existingItem->pivot->quantity + $request->quantity
            ]);
        } else {
            $user->items()->attach($item->id, ['quantity' => $request->quantity]);
        }

        return response()->json(['message' => 'Ítem afegit correctament a la motxilla!']);
    }

    // 3. Lògica per donar el Xuxemon Aleatori
    public function giveRandomXuxemon(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // Agafem un Xuxemon qualsevol de la base de dades
        $randomXuxemon = Xuxemon::inRandomOrder()->first();

        // Comprovació defensiva: si no hi ha cap Xuxemon creat a la BD,
        // retornem un 404 en lloc de fallar silenciosament.
        if (!$randomXuxemon) {
            return response()->json(['error' => 'No hi ha cap Xuxemon creat a la BBDD!'], 404);
        }

        // attach() crea una nova fila a user_xuxemons sense camps pivot addicionals.
        // Cada attach() crea una instància independent del Xuxemon per a l'usuari.
        $user->xuxemons()->attach($randomXuxemon->id);

        return response()->json(['message' => 'Has regalat el Xuxemon: ' . $randomXuxemon->name]);
    }

    // --- LLEGIR CONFIGURACIONS GLOBALS ---
    public function getSettings()
    {
        // Retornem un objecte clau-valor fàcil de llegir per Angular
        $settings = Setting::pluck('value', 'key');
        return response()->json($settings);
    }

    // --- GUARDAR CONFIGURACIONS GLOBALS ---
    public function updateSettings(Request $request)
    {
        // Validem que ens enviïn els 3 valors i siguin números entre 0 i 100
        $validated = $request->validate([
            'atracon_prob'    => 'required|integer|min:0|max:100',
            'sobredosis_prob' => 'required|integer|min:0|max:100',
            'bajon_prob'      => 'required|integer|min:0|max:100',
        ]);

        // Guardem o actualitzem cada paràmetre a la base de dades
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => '⚙️ Configuració global del joc actualitzada amb èxit!']);
    }
}