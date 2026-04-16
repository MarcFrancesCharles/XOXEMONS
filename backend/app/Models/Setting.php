<?php

/**
 * ============================================================
 * FITXER: app/Models/Setting.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model de configuració global del joc. Emmagatzema parells
 *   clau-valor que l'administrador pot modificar en temps real
 *   des del panell d'admin. Actualment conté les probabilitats
 *   de malalties que XuxemonController llegeix en cada alimentació.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: settings (migració: 2026_04_09_151255_create_settings_table.php)
 *   → Escrit per: AdminController (updateSettings)
 *   → Llegit per: XuxemonController (feed, sistema d'infecció) i AdminController (getSettings)
 *   → Inicialitzat per: SettingSeeder (valors per defecte)
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * Camps assignables massivament.
     *
     * Columnes:
     *   - key    (string unique: nom del paràmetre, ex: 'atracon_prob')
     *   - value  (integer: valor del paràmetre, ex: 10 → 10% de probabilitat)
     *
     * S'usa updateOrCreate() als controladors per garantir que la clau sigui
     * sempre única i que si no existeix es creï en el primer ús.
     */
    protected $fillable = ['key', 'value'];
}