<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/InventoryController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest controlador actua com a punt de consulta per a la 
 *   motxilla (inventari) de l'usuari. Permet llegir quins ítems 
 *   posseeix el jugador i en quina quantitat, dades que s'utilitzen 
 *   tant per a la visualització de l'inventari com per a la lògica 
 *   d'ús d'objectes en altres controladors.
 *
 * FUNCIONALITATS:
 *   - Llistar tots els ítems (xuxes i vacunes) del jugador.
 *   - Exposar les dades de la taula pivot 'user_items' (quantitats).
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (accés a la relació items())
 *   → Taula: user_items (font de veritat de les quantitats per usuari)
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Retorna la llista d'ítems de la motxilla de l'usuari autenticat.
     * 
     * Gràcies a la relació BelongsToMany definida al model User amb la clàusula 
     * ->withPivot('quantity'), cada objecte d'ítem retornat inclou una 
     * propietat 'pivot' que conté la quantitat real que el jugador té en stock.
     */
    public function index()
    {
        // Recuperem l'usuari loguejat a través del Guard de la API.
        $user = Auth::user();

        // Accedim a la col·lecció d'ítems. Eloquent farà automàticament el 
        // JOIN amb la taula 'user_items' i 'items' per portar tant les dades 
        // base (nom, tipus) com les dades de relació (quantitat).
        $items = $user->items;

        // Retornem la col·lecció en format JSON. El client d'Angular processarà 
        // l'array d'ítems per pintar la pantalla de la motxilla.
        return response()->json($items);
    }
}