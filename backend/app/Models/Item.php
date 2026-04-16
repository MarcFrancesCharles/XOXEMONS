<?php

/**
 * ============================================================
 * FITXER: app/Models/Item.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Representa el catàleg d'objectes del joc: xuxes (aliment pels
 *   Xuxemons) i vacunes (remei per a les malalties). Funciona com
 *   a taula de referència: les instàncies a la motxilla es gestionen
 *   a la taula pivot user_items.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: items (migració: 2026_03_12_181116_create_items_table.php)
 *   → Taula pivot: user_items (via la relació items() de User)
 *   → Usat per: AdminController (firstOrCreate i giveItem),
 *     XuxemonController (verificar disponibilitat i consumir ítems),
 *     AuthController (recompensa diària)
 *   → Omplert per: ItemSeeder (6 xuxes + 3 vacunes)
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * Camps assignables massivament.
     * 'image' s'inclou perquè AdminController pot crear nous ítems
     * via firstOrCreate() i pot necessitar establir-la.
     */
    protected $fillable = [
        'name',
        'type',         // 'xuxe' o 'vacuna'
        'is_stackable', // true per xuxes (s'apilen de 5 en 5), false per vacunes
        'image'
    ];
}