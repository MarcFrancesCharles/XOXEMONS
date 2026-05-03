<?php

/**
 * ============================================================
 * FITXER: database/seeders/XuxemonSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix el catàleg mestre de criatures (Xuxemons). Aquest 
 *   seeder crea 18 espècies base que serveixen com a plantilla 
 *   per a les criatures dels jugadors. 
 *
 * ESTRUCTURA D'EVOLUCIÓ:
 *   Les espècies s'organitzen en 3 elements × 2 famílies × 3 mides:
 *   - Aigua: Famílies Gotiró i Xopar.
 *   - Terra: Famílies Terrós i Llimot.
 *   - Aire: Famílies Brisot i Bufet.
 *
 * MECÀNICA: 
 *   L'evolució (Petit → Mitja → Gran) es realitza buscant en aquest 
 *   catàleg l'espècie amb el mateix nom/tipus però la següent mida.
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class XuxemonSeeder extends Seeder
{
    /**
     * Executa el seeder del catàleg de Xuxemons.
     */
    public function run(): void
    {
        $now = now();

        $xuxemons = [
            // ── TIPUS AIGUA ────────────────────────────────────────────────
            
            // Família 1: Gotiró
            ['name' => 'Gotiró',    'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/gotiro.png'],
            ['name' => 'Bassiol',   'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/bassiol.png'],
            ['name' => 'Maregot',   'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/maregot.png'],
            // Família 2: Xopar
            ['name' => 'Xopar',     'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/xopar.png'],
            ['name' => 'Remull',    'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/remull.png'],
            ['name' => 'Tempestot', 'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/tempestot.png'],

            // ── TIPUS TERRA ────────────────────────────────────────────────
            
            // Família 1: Terrós
            ['name' => 'Terrós',    'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/terros.png'],
            ['name' => 'Pedrot',    'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/pedrot.png'],
            ['name' => 'Rocallot',  'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/rocallot.png'],
            // Família 2: Llimot
            ['name' => 'Llimot',    'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/llimot.png'],
            ['name' => 'Argillós',  'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/argillos.png'],
            ['name' => 'Granitor',  'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/granitor.png'],

            // ── TIPUS AIRE ─────────────────────────────────────────────────
            
            // Família 1: Brisot
            ['name' => 'Brisot',    'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/brisot.png'],
            ['name' => 'Ventell',   'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/ventell.png'],
            ['name' => 'Torbelló',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/torbello.png'],
            // Família 2: Bufet
            ['name' => 'Bufet',     'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/bufet.png'],
            ['name' => 'Ratxada',   'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/ratxada.png'],
            ['name' => 'Huracano',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/huracano.png'],
        ];

        // Inserim totes les espècies a la taula 'xuxemons'.
        foreach ($xuxemons as &$x) {
            $x['created_at'] = $now;
            $x['updated_at'] = $now;
        }

        DB::table('xuxemons')->insert($xuxemons);
    }
}