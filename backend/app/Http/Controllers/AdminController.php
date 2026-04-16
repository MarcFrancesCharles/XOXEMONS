<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;

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
            'user_id' => 'required|exists:users,id',
            'item_type' => 'required|in:xuxe,vacuna',
            'item_name' => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($request->user_id);

        // Busquem si l'ítem ja existeix a la BBDD, si no, el creem
        $item = Item::firstOrCreate(
            ['name' => $request->item_name, 'type' => $request->item_type],
            ['is_stackable' => $request->item_type === 'xuxe'] // Les xuxes s'apilen, les vacunes no
        );

        $totalSlotsUsed = 0;
        foreach ($user->items as $userItem) {
            if ($userItem->is_stackable) {
                // Si és apilable (xuxe), dividim per 5 i arrodonim cap amunt
                $totalSlotsUsed += ceil($userItem->pivot->quantity / 5);
            } else {
                // Si no és apilable (vacuna), cadascuna ocupa 1 espai
                $totalSlotsUsed += $userItem->pivot->quantity;
            }
        }

        // Si l'inventari ja està ple (20 espais o més), bloquegem i no donem res
        if ($totalSlotsUsed >= 20) {
            return response()->json([
                'error' => 'La motxilla daquest jugador està plena (20/20 espais). Els objectes han estat descartats.'
            ], 400);
        }

        // Comprovem si l'usuari ja té aquest objecte a la seva motxilla
        $existingItem = $user->items()->where('item_id', $item->id)->first();

        if ($existingItem) {
            // Si ja el té, li sumem la quantitat
            $user->items()->updateExistingPivot($item->id, [
                'quantity' => $existingItem->pivot->quantity + $request->quantity
            ]);
        } else {
            // Si no el té, creem la relació nova
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
        $randomXuxemon = \App\Models\Xuxemon::inRandomOrder()->first();

        if (!$randomXuxemon) {
            return response()->json(['error' => 'No hi ha cap Xuxemon creat a la BBDD!'], 404);
        }

        // L'afegim a la col·lecció de l'usuari
        $user->xuxemons()->attach($randomXuxemon->id);

        return response()->json(['message' => 'Has regalat el Xuxemon: ' . $randomXuxemon->name]);
    }

    // --- LLEGIR CONFIGURACIONS GLOBALS ---
    public function getSettings()
    {
        // Retornem un objecte clau-valor fàcil de llegir per Angular
        $settings = \App\Models\Setting::pluck('value', 'key');
        return response()->json($settings);
    }

    // --- GUARDAR CONFIGURACIONS GLOBALS ---
    public function updateSettings(Request $request)
    {
        // Validem que ens enviïn els 3 valors i siguin números entre 0 i 100
        $validated = $request->validate([
            'atracon_prob' => 'required|integer|min:0|max:100',
            'sobredosis_prob' => 'required|integer|min:0|max:100',
            'bajon_prob' => 'required|integer|min:0|max:100',
        ]);

        // Guardem o actualitzem cada paràmetre a la base de dades
        foreach ($validated as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => '⚙️ Configuració global del joc actualitzada amb èxit!']);
    }
}