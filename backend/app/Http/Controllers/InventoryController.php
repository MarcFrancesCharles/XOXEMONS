<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/InventoryController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Controlador lleuger que exposa la motxilla de l'usuari autenticat.
 *   Actua com a punt de lectura de l'inventari que Angular mostra
 *   a la pantalla de motxilla, i del qual XuxemonController llegeix
 *   per verificar la disponibilitat de xuxes i vacunes.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (via Auth::user(), accés a items via la relació)
 *   → Model: App\Models\Item (inclòs automàticament via la relació items())
 *   → Taula pivot: user_items (quantity de cada ítem per usuari)
 *   → Cridat des de: routes/api.php (GET /inventory)
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Retorna tots els ítems de la motxilla de l'usuari autenticat.
     *
     * La relació items() del model User ja inclou ->withPivot('quantity'),
     * per tant la quantitat de cada ítem apareix automàticament en la resposta
     * dins d'un objecte 'pivot' per a cada ítem.
     */
    public function index()
    {
        // Auth::user()->items accedeix a la relació BelongsToMany definida al model User,
        // que fa un JOIN de user_items i items i retorna la col·lecció completa
        // amb la quantitat de cada ítem inclosa al camp pivot.quantity.
        $items = Auth::user()->items;
        return response()->json($items);
    }
}