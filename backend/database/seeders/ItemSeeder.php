<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Insereix tots els ítems del joc.
     *
     * Tipus:
     *  - xuxe   : apilable (is_stackable = true)  → s'utilitzen per alimentar Xuxemons
     *  - vacuna : no apilable (is_stackable = false) → s'utilitzen per curar malalties
     *
     * Malalties possibles que curen les vacunes (definides en SettingSeeder):
     *   · Atracón
     *   · Sobredosis de Sucre
     *   · Bajón de Azúcar
     */
    public function run(): void
    {
        $now = now();

        $items = [
            // ── XUXES (apilables) ─────────────────────────────────────────────
            ['name' => 'Xuxe de Maduixa',   'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_maduixa.png'],
            ['name' => 'Xuxe de Llimona',   'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_llimona.png'],
            ['name' => 'Xuxe de Taronja',   'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_taronja.png'],
            ['name' => 'Xuxe de Raïm',      'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_raim.png'],
            ['name' => 'Xuxe de Sandía',    'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_sandia.png'],
            ['name' => 'Xuxe de Colà',      'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_cola.png'],

            // ── VACUNES (no apilables) ────────────────────────────────────────
            ['name' => 'Vacuna Antiglotona',    'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_atracon.png'],
            ['name' => 'Vacuna Antisucre',      'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_sobredosis.png'],
            ['name' => 'Vacuna Energitzant',    'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_bajon.png'],
        ];

        foreach ($items as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('items')->insert($items);
    }
}
