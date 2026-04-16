<?php

/**
 * ============================================================
 * FITXER: app/Models/UserItem.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model corresponent a la taula pivot user_items, que relaciona
 *   usuaris amb els seus ítems i n'emmagatzema la quantitat.
 *   A diferència de UserXuxemon, aquest model pivot NO s'usa
 *   directament als controladors: les operacions es fan via la
 *   relació items() del model User (attach, updateExistingPivot).
 *   El model existeix per si en el futur cal accedir-hi directament.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: user_items (migració: 2026_03_12_181116_create_user_items_table.php)
 *   → Gestionat indirectament per: User::items() (BelongsToMany)
 *   → Usat indirectament per: AdminController, XuxemonController,
 *     AuthController, InventoryController
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserItem extends Model
{
    // Model bàsic sense $fillable ni relacions directes perquè
    // tota la gestió es fa via la relació BelongsToMany de User.
    // Si en el futur cal fer consultes directes a user_items
    // (ex: per a estadístiques), s'afegirà aquí la configuració necessària.
}