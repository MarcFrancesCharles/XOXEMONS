<?php

/**
 * ============================================================
 * FITXER: app/Models/Item.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Representa els objectes consumibles del joc que els jugadors 
 *   guarden a la seva motxilla. Principalment es divideixen en 
 *   dues categories: aliments (xuxes) i medicaments (vacunes).
 *
 * PROPIETATS CLAU:
 *   - Type: Defineix si l'ítem és una 'xuxe' o una 'vacuna'.
 *   - Is_stackable: Determina si l'ítem es pot apilar en grups 
 *     de 5 a la motxilla (estalviant espai).
 *
 * MAPA DE CONNEXIONS:
 *   → Relacionat amb User via la taula pivot user_items.
 *   → Usat en controladors d'alimentació, salut i administració.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * Camps que es poden omplir massivament.
     * 
     * - name: Nom de l'ítem (ex: 'Xocolatina', 'Poma').
     * - type: Tipus d'ítem (xuxe o vacuna).
     * - is_stackable: Booleà que indica si és apilable.
     */
    protected $fillable = [
        'name',
        'type',
        'is_stackable'
    ];

    /**
     * Configuració de la conversió de tipus.
     * 
     * Ens assegurem que 'is_stackable' es tracti sempre com un booleà 
     * per facilitar les validacions de capacitat a l'AdminController.
     */
    protected $casts = [
        'is_stackable' => 'boolean'
    ];
}