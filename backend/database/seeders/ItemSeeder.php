<?php

/**
 * ============================================================
 * FITXER: database/seeders/ItemSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Emplena el catàleg d'objectes del joc: 6 xuxes (aliments apilables)
 *   i 3 vacunes (remeis no apilables). Estableix les dades de referència
 *   que AdminController, AuthController i XuxemonController consulten.
 *
 *   IMPORTANT: els noms de les vacunes han de coincidir EXACTAMENT
 *   amb els que XuxemonController::vaccinate() compara per determinar
 *   la compatibilitat vacuna ↔ malaltia.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: items
 *   → Llegit per: XuxemonController (vaccinate, verificar disponibilitat),
 *     AuthController (recompensa diària), AdminController (give-item)
 *   → Prerequisit de: UserItemSeeder
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            // ── XUXES (apilables: is_stackable = true) ────────────────────
            // Les xuxes s'apilen de 5 en 5 per espai de motxilla.
            // El nom del sabor és cosmètic; totes funcionen igual per alimentar.
            ['name' => 'Xuxe de Maduixa', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_maduixa.png'],
            ['name' => 'Xuxe de Llimona', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_llimona.png'],
            ['name' => 'Xuxe de Taronja', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_taronja.png'],
            ['name' => 'Xuxe de Raïm',    'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_raim.png'],
            ['name' => 'Xuxe de Sandía',  'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_sandia.png'],
            ['name' => 'Xuxe de Colà',    'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_cola.png'],

            // ── VACUNES (no apilables: is_stackable = false) ──────────────
            // Cada vacuna ocupa 1 espai de motxilla per unitat.
            // XuxemonController::vaccinate() compara el 'name' d'aquests ítems
            // amb la malaltia del Xuxemon per decidir si la cura.
            ['name' => 'Vacuna Antiglotona',  'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_atracon.png'],   // Cura: Atracón
            ['name' => 'Vacuna Antisucre',    'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_sobredosis.png'], // Cura: Sobredosis de sucre
            ['name' => 'Vacuna Energitzant',  'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_bajon.png'],      // Cura: Bajón de azúcar
        ];

        foreach ($items as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('items')->insert($items);
    }
}