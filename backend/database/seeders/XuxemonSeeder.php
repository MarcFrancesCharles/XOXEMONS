<?php

/**
 * ============================================================
 * FITXER: database/seeders/XuxemonSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix el catàleg mestre de criatures (Xuxemons). Aquest 
 *   seeder crea 9 espècies base amb els seus 3 tamanys (27 registres).
 *
 * ESTRUCTURA:
 *   public/assets/xuxemons/{tipus}/{nom}_{mida}.ext
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
            // ── TIPUS AIGUA ────────────────────────────────────────────────
            
            // Família 1
            ['name' => 'Cangrexor', 'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/aigua/cangrexor_petit.webp'],
            ['name' => 'Cangrexor', 'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/aigua/cangrexor_mitja.webp'],
            ['name' => 'Cangrexor', 'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/aigua/cangrexor_gran.webp'],
            
            // Família 2
            ['name' => 'Crocorox',  'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/aigua/crocorox_petit.webp'],
            ['name' => 'Crocorox',  'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/aigua/crocorox_mitja.webp'],
            ['name' => 'Crocorox',  'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/aigua/crocorox_gran.webp'],

            // Família 3 
            ['name' => 'Pezcadoz',  'type' => 'Aigua', 'size' => 'Petit', 'image' => '/assets/xuxemons/aigua/pezcadoz_petit.webp'],
            ['name' => 'Pezcadoz',  'type' => 'Aigua', 'size' => 'Mitja', 'image' => '/assets/xuxemons/aigua/pezcadoz_mitja.webp'],
            ['name' => 'Pezcadoz',  'type' => 'Aigua', 'size' => 'Gran',  'image' => '/assets/xuxemons/aigua/pezcadoz_gran.webp'],


            // ── TIPUS TERRA ────────────────────────────────────────────────
            
            // Família 1
            ['name' => 'Serpienterix', 'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/terra/serpienterix_petit.webp'],
            ['name' => 'Serpienterix', 'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/terra/serpienterix_mitja.webp'],
            ['name' => 'Serpienterix', 'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/terra/serpienterix_gran.webp'],
            
            // Família 2
            ['name' => 'Tyrannorox', 'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/terra/tyrannorox_petit.webp'],
            ['name' => 'Tyrannorox', 'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/terra/tyrannorox_mitja.webp'],
            ['name' => 'Tyrannorox', 'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/terra/tyrannorox_gran.webp'],

            // Família 3
            ['name' => 'Vacarox', 'type' => 'Terra', 'size' => 'Petit', 'image' => '/assets/xuxemons/terra/vacarox_petit.webp'],
            ['name' => 'Vacarox', 'type' => 'Terra', 'size' => 'Mitja', 'image' => '/assets/xuxemons/terra/vacarox_mitja.webp'],
            ['name' => 'Vacarox', 'type' => 'Terra', 'size' => 'Gran',  'image' => '/assets/xuxemons/terra/vacarox_gran.webp'],


            // ── TIPUS AIRE ─────────────────────────────────────────────────
            
            // Família 1
            ['name' => 'Colibrirox', 'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/aire/colibrirox_petit.webp'],
            ['name' => 'Colibrirox', 'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/aire/colibrirox_mitja.webp'],
            ['name' => 'Colibrirox', 'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/aire/colibrirox_gran.webp'],
            
            // Família 2
            ['name' => 'Pelicanox',  'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/aire/pelicanox_petit.webp'],
            ['name' => 'Pelicanox',  'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/aire/pelicanox_mitja.webp'],
            ['name' => 'Pelicanox',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/aire/pelicanox_gran.webp'],

            // Família 3 
            ['name' => 'Tucanrox',  'type' => 'Aire',  'size' => 'Petit', 'image' => '/assets/xuxemons/aire/tucanrox_petit.webp'],
            ['name' => 'Tucanrox',  'type' => 'Aire',  'size' => 'Mitja', 'image' => '/assets/xuxemons/aire/tucanrox_mitja.webp'],
            ['name' => 'Tucanrox',  'type' => 'Aire',  'size' => 'Gran',  'image' => '/assets/xuxemons/aire/tucanrox_gran.webp'],
        ];

        // Inserim totes les espècies a la taula 'xuxemons'.
        foreach ($xuxemons as &$x) {
            $x['created_at'] = $now;
            $x['updated_at'] = $now;
        }

        DB::table('xuxemons')->insert($xuxemons);   
    }
}