<?php

/**
 * ============================================================
 * FITXER: app/Models/Xuxemon.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Representa el catàleg de criatures del joc. Cada fila és
 *   un "tipus" de Xuxemon (no una instància d'un jugador).
 *   La relació entre jugadors i Xuxemons es gestiona via la
 *   taula pivot user_xuxemons.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: xuxemons (migració: 2026_03_12_181115_create_xuxemons_table.php)
 *   → Taula pivot: user_xuxemons (a través de la relació de User)
 *   → Usat per: XuxemonController (evolució), AuthController (recompensa diària),
 *     AdminController (donar Xuxemon), BattleController (dades de batalla)
 *   → Omplert per: XuxemonSeeder (18 criatures: 3 tipus × 3 mides × 2 famílies)
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Xuxemon extends Model
{
    // No definim $fillable perquè Xuxemon no es crea via formulari de l'usuari;
    // s'insereix al seeder via DB::table()->insert() o es consulta per evolució.
    // El catàleg de Xuxemons és dades de joc estàtiques gestionades per l'admin.
    //
    // Columnes disponibles a la taula xuxemons:
    //   - id
    //   - name     (ex: 'Gotiró', 'Bassiol', 'Maregot')
    //   - type     (enum: 'Aigua' | 'Terra' | 'Aire')
    //   - size     (enum: 'Petit' | 'Mitja' | 'Gran')
    //   - image    (ruta relativa a l'asset, ex: '/assets/xuxemons/gotiro.png')
    //   - created_at, updated_at
}