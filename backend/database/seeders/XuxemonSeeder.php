<?php

/**
 * ============================================================
 * FITXER: database/seeders/XuxemonSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Emplena el catàleg de Xuxemons del joc: 18 criatures organitzades
 *   en 3 tipus (Aigua, Terra, Aire) × 3 mides (Petit, Mitja, Gran)
 *   × 2 famílies d'evolució per tipus.
 *
 *   Estructura d'evolució:
 *     Gotiró (Petit Aigua) → Bassiol (Mitja Aigua) → Maregot (Gran Aigua)
 *     Xopar  (Petit Aigua) → Remull  (Mitja Aigua) → Tempestot (Gran Aigua)
 *     [i així per Terra i Aire]
 *
 *   XuxemonController::feed() usa tipus+mida per trobar el Xuxemon
 *   evolucionat correcte.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: xuxemons
 *   → Llegit per: XuxemonController (evolució), AuthController (recompensa diària),
 *     AdminController (donar aleatori), BattleController (dades de batalla)
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class XuxemonSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $xuxemons = [
            // ── TIPUS AIGUA ───────────────────────────────────────────────
            // Família 1: Gotiró → Bassiol → Maregot
            ['name' => 'Gotiró',    'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/gotiro.png'],
            ['name' => 'Bassiol',   'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/bassiol.png'],
            ['name' => 'Maregot',   'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/maregot.png'],
            // Família 2: Xopar → Remull → Tempestot
            ['name' => 'Xopar',     'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/xopar.png'],
            ['name' => 'Remull',    'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/remull.png'],
            ['name' => 'Tempestot', 'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/tempestot.png'],

            // ── TIPUS TERRA ───────────────────────────────────────────────
            // Família 1: Terrós → Pedrot → Rocallot
            ['name' => 'Terrós',    'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/terros.png'],
            ['name' => 'Pedrot',    'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/pedrot.png'],
            ['name' => 'Rocallot',  'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/rocallot.png'],
            // Família 2: Llimot → Argillós → Granitor
            ['name' => 'Llimot',    'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/llimot.png'],
            ['name' => 'Argillós',  'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/argillos.png'],
            ['name' => 'Granitor',  'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/granitor.png'],

            // ── TIPUS AIRE ────────────────────────────────────────────────
            // Família 1: Brisot → Ventell → Torbelló
            ['name' => 'Brisot',    'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/brisot.png'],
            ['name' => 'Ventell',   'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/ventell.png'],
            ['name' => 'Torbelló',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/torbello.png'],
            // Família 2: Bufet → Ratxada → Huracano
            ['name' => 'Bufet',     'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/bufet.png'],
            ['name' => 'Ratxada',   'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/ratxada.png'],
            ['name' => 'Huracano',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/huracano.png'],
        ];

        // Afegim timestamps a cada fila per complir amb les columnes de la taula.
        // Usem DB::table()->insert() en lloc de Model::create() per eficiència:
        // inserim tots els registres en una sola consulta SQL.
        foreach ($xuxemons as &$x) {
            $x['created_at'] = $now;
            $x['updated_at'] = $now;
        }

        DB::table('xuxemons')->insert($xuxemons);
    }
}