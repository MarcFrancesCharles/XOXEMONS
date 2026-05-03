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
            
            ['name' => 'Xuxe de Maduixa', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxes/xuxe_maduixa.webp'],
            ['name' => 'Xuxe de Taronja', 'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxes/xuxe_taronja.webp'],
            ['name' => 'Xuxe de Raïm',    'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxes/xuxe_raim.webp'],
            ['name' => 'Xuxe de Poma',  'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxes/xuxe_poma.webp'],
            ['name' => 'Xuxe de Mango',    'type' => 'xuxe',   'is_stackable' => true,  'image' => '/assets/items/xuxes/xuxe_mango.webp'],

            // ── VACUNES (Medicaments no apilables) ───────────────────
            
            // Cada vacuna està dissenyada per curar una malaltia concreta.
            ['name' => 'Xocolatina',  'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacunes/xocolatina.webp'],
            ['name' => 'Xal de fruites',    'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacunes/xal_de_fruites.webp'],
            ['name' => 'Inxulina',  'type' => 'vacuna', 'is_stackable' => false, 'image' => '/assets/items/vacunes/inxulina.webp'],
        ];

        // Inserim els ítems a la taula 'items'.
        foreach ($items as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('items')->insert($items);
    }
}