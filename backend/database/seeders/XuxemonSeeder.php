<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class XuxemonSeeder extends Seeder
{
    /**
     * Insereix tots els Xuxemons del joc.
     *
     * Cada Xuxemon té:
     *  - name  : nom únic
     *  - type  : Aigua | Terra | Aire
     *  - size  : Petit | Mitja | Gran  (línies d'evolució independents)
     *  - image : ruta a /assets/xuxemons/<nom>.png  (nullable)
     *
     * Els Xuxemons de mida Gran no s'assignen per defecte a cap jugador;
     * s'aconsegueixen evolucionant els de mida Mitja.
     */
    public function run(): void
    {
        $now = now();

        $xuxemons = [
            // ── TIPUS AIGUA ───────────────────────────────────────────────────
            ['name' => 'Gotiró',    'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/gotiro.png'],
            ['name' => 'Bassiol',   'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/bassiol.png'],
            ['name' => 'Maregot',   'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/maregot.png'],

            ['name' => 'Xopar',     'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/xopar.png'],
            ['name' => 'Remull',    'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/remull.png'],
            ['name' => 'Tempestot', 'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/tempestot.png'],

            // ── TIPUS TERRA ───────────────────────────────────────────────────
            ['name' => 'Terrós',    'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/terros.png'],
            ['name' => 'Pedrot',    'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/pedrot.png'],
            ['name' => 'Rocallot',  'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/rocallot.png'],

            ['name' => 'Llimot',    'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/llimot.png'],
            ['name' => 'Argillós',  'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/argillos.png'],
            ['name' => 'Granitor',  'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/granitor.png'],

            // ── TIPUS AIRE ────────────────────────────────────────────────────
            ['name' => 'Brisot',    'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/brisot.png'],
            ['name' => 'Ventell',   'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/ventell.png'],
            ['name' => 'Torbelló',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/torbello.png'],

            ['name' => 'Bufet',     'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/bufet.png'],
            ['name' => 'Ratxada',   'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/ratxada.png'],
            ['name' => 'Huracano',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/huracano.png'],
        ];

        foreach ($xuxemons as &$x) {
            $x['created_at'] = $now;
            $x['updated_at'] = $now;
        }

        DB::table('xuxemons')->insert($xuxemons);
    }
}
