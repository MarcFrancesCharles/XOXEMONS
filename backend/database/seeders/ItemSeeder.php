<?php

/**
 * ============================================================
 * FITXER: database/seeders/ItemSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix el catàleg d'objectes consumibles de l'aplicació. 
 *   Crea els dos tipus d'ítems fonamentals: aliments (xuxes) 
 *   i medicaments (vacunes).
 *
 * DETALLS DELS ÍTEMS:
 *   - Xuxes: Aliments apilables (stacks de 5) utilitzats per l'evolució.
 *   - Vacunes: Medicaments no apilables utilitzats per curar malalties 
 *     específiques.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per XuxemonController (alimentació i vacunació).
 *   → Usat per AdminController (gestió de motxilla).
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Executa el seeder del catàleg d'ítems.
     */
    public function run(): void
    {
        $now = now();

        $items = [
            // ── XUXES (Aliments apilables) ───────────────────────────
            
            ['name' => 'Xuxe de Maduixa', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_maduixa.png'],
            ['name' => 'Xuxe de Llimona', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_llimona.png'],
            ['name' => 'Xuxe de Taronja', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_taronja.png'],
            ['name' => 'Xuxe de Raïm',    'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_raim.png'],
            ['name' => 'Xuxe de Sandía',  'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_sandia.png'],
            ['name' => 'Xuxe de Colà',    'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxe_cola.png'],

            // ── VACUNES (Medicaments no apilables) ───────────────────
            
            // Cada vacuna està dissenyada per curar una malaltia concreta.
            ['name' => 'Vacuna Antiglotona',  'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_atracon.png'],
            ['name' => 'Vacuna Antisucre',    'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_sobredosis.png'],
            ['name' => 'Vacuna Energitzant',  'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacuna_bajon.png'],
        ];

        // Inserim els ítems a la taula 'items'.
        foreach ($items as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('items')->insert($items);
    }
}