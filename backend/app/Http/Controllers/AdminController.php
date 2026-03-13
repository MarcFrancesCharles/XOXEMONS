<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;

class AdminController extends Controller
{
    // 1. Retornem els usuaris per omplir el <select>
    public function getUsers() {
        // Retornem només els camps necessaris
        return response()->json(User::select('id', 'name', 'custom_id')->get());
    }

    // 2. Lògica per donar l'objecte al jugador
    public function giveItem(Request $request) {
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
    
    public function giveRandomXuxemon(Request $request) {
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
}