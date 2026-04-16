<?php

/**
 * ============================================================
 * FITXER: app/Models/UserXuxemon.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model de la taula pivot user_xuxemons que representa una
 *   instància concreta d'un Xuxemon en possessió d'un jugador.
 *   Conté l'estat individual de cada Xuxemon: quant ha menjat
 *   i si està malalt. És el model central de la mecànica de joc.
 *
 *   IMPORTANT: Hereta de Pivot (no de Model) perquè és una taula
 *   pivot "custom" amb columnes extra, una pràctica estàndard de Laravel
 *   per a pivots que necessiten ser tractats com a models propis.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: user_xuxemons (migració: create + add_fields)
 *   → Referenciat per: App\Models\User (relació xuxemons())
 *   → Usat directament per: XuxemonController (feed, vaccinate),
 *     BattleController (getBattleData, transferXuxemon),
 *     AuthController (recompensa diària)
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserXuxemon extends Pivot
{
    // Declarem la taula explícitament per evitar que Laravel intenti inferir-la
    // com 'user_xuxemon' (singular sense la 's' final).
    protected $table = 'user_xuxemons';

    /**
     * Tots els camps són assignables perquè UserXuxemon es crea i modifica
     * sovint des dels controladors (feed, vaccinate, daily-reward, seeders).
     *
     * Columnes:
     *   - user_id     (FK → users.id)
     *   - xuxemon_id  (FK → xuxemons.id, canvia en evolució)
     *   - food_eaten  (int: quantes xuxes ha menjat des de l'última evolució)
     *   - disease     (string|null: malaltia actual o null si està sa)
     */
    protected $fillable = [
        'user_id',
        'xuxemon_id',
        'food_eaten',
        'disease'
    ];
}