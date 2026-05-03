<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Xuxemon;
use App\Models\Setting;

class AdminController extends Controller
{
    public function getUsers()
    {
        $users = User::select('id', 'name', 'custom_id')->get();
        return response()->json($users);
    }

    public function giveItem(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'item_type' => 'required|in:xuxe,vacuna',
            'item_name' => 'required|string',
            'quantity'  => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($request->user_id);

        $item = Item::firstOrCreate(
            ['name' => $request->item_name, 'type' => $request->item_type],
            ['is_stackable' => $request->item_type === 'xuxe']
        );

        // Càlcul dels slots actuals
        $totalSlotsUsed = 0;
        foreach ($user->items as $userItem) {
            if ($userItem->is_stackable) {
                $totalSlotsUsed += ceil($userItem->pivot->quantity / 5);
            } else {
                $totalSlotsUsed += $userItem->pivot->quantity;
            }
        }

        // Càlcul dels slots nous que ocuparà el nou ítem
        $newSlotsNeeded = 0;
        if ($item->is_stackable) {
            $existingItem = $user->items()->where('item_id', $item->id)->first();
            $existingQty  = $existingItem ? $existingItem->pivot->quantity : 0;
            $slotsAns     = $existingQty > 0 ? ceil($existingQty / 5) : 0;
            $slotsNou     = ceil(($existingQty + $request->quantity) / 5);
            $newSlotsNeeded = $slotsNou - $slotsAns;
        } else {
            $newSlotsNeeded = $request->quantity;
        }

        if (($totalSlotsUsed + $newSlotsNeeded) > 20) {
            return response()->json([
                'error' => 'La motxilla del jugador no té prou espai. Slots disponibles: ' . (20 - $totalSlotsUsed)
            ], 400);
        }

        $existingItem = $user->items()->where('item_id', $item->id)->first();

        if ($existingItem) {
            $user->items()->updateExistingPivot($item->id, [
                'quantity' => $existingItem->pivot->quantity + $request->quantity
            ]);
        } else {
            $user->items()->attach($item->id, ['quantity' => $request->quantity]);
        }

        return response()->json(['message' => 'Ítem entregat i registrat a la motxilla correctament.']);
    }

    public function giveRandomXuxemon(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        $randomXuxemon = Xuxemon::where('size', 'Petit')->inRandomOrder()->first();

        if (!$randomXuxemon) {
            return response()->json(['error' => 'No hi ha Xuxemons definits al catàleg del sistema.'], 404);
        }

        $user->xuxemons()->attach($randomXuxemon->id, ['food_eaten' => 0, 'disease' => null]);

        return response()->json([
            'message' => "L'usuari ha rebut un exemplar de l'espècie: " . $randomXuxemon->name
        ]);
    }

    public function getSettings()
    {
        $settings = Setting::pluck('value', 'key');
        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'atracon_prob'    => 'required|integer|min:0|max:100',
            'sobredosis_prob' => 'required|integer|min:0|max:100',
            'bajon_prob'      => 'required|integer|min:0|max:100',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'Paràmetres globals de dificultat actualitzats correctament.']);
    }
}