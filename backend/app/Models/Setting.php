<?php

/**
 * ============================================================
 * FITXER: app/Models/Setting.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model que gestiona la configuració global del sistema en 
 *   format clau-valor. S'utilitza principalment per emmagatzemar 
 *   les probabilitats de malaltia del joc.
 *
 * FUNCIONALITATS CLAU:
 *   - Permetre a l'administrador ajustar el balanceig del joc 
 *     sense modificar el codi.
 *   - Persistir valors com 'atracon_prob', 'sobredosis_prob', etc.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per l'AdminController per llegir i desar valors.
 *   → Consultat pel XuxemonController en cada alimentació.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * Camps que es poden omplir massivament.
     * 
     * - key: El nom del paràmetre de configuració (identificador únic).
     * - value: El valor numèric o de text associat a la configuració.
     */
    protected $fillable = [
        'key',
        'value'
    ];

    /**
     * Configuració de la conversió de tipus.
     * 
     * El valor s'emmagatzema com a string a la BD, però normalment 
     * el tractarem com un enter per a les probabilitats.
     */
    protected $casts = [
        'value' => 'integer'
    ];
}