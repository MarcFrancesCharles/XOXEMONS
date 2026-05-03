<?php

/**
 * ============================================================
 * FITXER: app/Models/UserItem.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model corresponent a la taula pivot 'user_items'. Aquesta 
 *   taula actua com el vincle entre els jugadors i els seus 
 *   consumibles (xuxes i vacunes).
 *
 * FUNCIONALITATS CLAU:
 *   - Mantenir el registre de quantes unitats de cada ítem té 
 *     cada usuari (motxilla).
 *
 * IMPORTANT: A diferència del pivot de Xuxemons, aquest model 
 * s'utilitza majoritàriament de forma indirecta a través de la 
 * relació items() definida al model User.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserItem extends Model
{
    /**
     * Aquest model està preparat per si en el futur es requereixen 
     * consultes directes a l'inventari (per exemple, per a rànquings 
     * d'objectes o auditories de motxilla).
     * 
     * Estructura de la taula:
     * - user_id: ID de l'usuari propietari.
     * - item_id: ID de l'ítem (xuxe/vacuna).
     * - quantity: Nombre d'unitats disponibles.
     */
}