<?php

/**
 * ============================================================
 * FITXER: app/Models/UserXuxemon.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest model representa la taula pivot 'user_xuxemons' que 
 *   connecta jugadors amb criatures. A diferència d'un pivot 
 *   estàndard, aquest model gestiona l'ESTAT VITAL de cada 
 *   instància: el nivell d'alimentació i la salut (malalties).
 *
 * MECÀNIQUES DE JOC GESTIONADES:
 *   - Propietat: Qui és el propietari actual de la criatura.
 *   - Alimentació (food_eaten): Acumulació per a l'evolució.
 *   - Salut (disease): Estat de malaltia que bloqueja accions.
 *
 * IMPORTANT: Estén de 'Pivot' per permetre una integració fluida 
 * amb les relacions BelongsToMany de Laravel.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserXuxemon extends Pivot
{
    // Forcem el nom de la taula per evitar discrepàncies amb el plural automàtic.
    protected $table = 'user_xuxemons';

    /**
     * Camps habilitats per a l'assignació.
     * 
     * - user_id: ID del jugador propietari.
     * - xuxemon_id: ID de l'espècie base (canvia quan el Xuxemon evoluciona).
     * - food_eaten: Unitats de menjar consumides des de l'últim estadi.
     * - disease: Nom de la malaltia activa o null si el Xuxemon està sa.
     */
    protected $fillable = [
        'user_id',
        'xuxemon_id',
        'food_eaten',
        'disease'
    ];
}