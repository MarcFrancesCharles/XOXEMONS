<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Insereix la configuració global de probabilitats de malalties.
     *
     * Les claus han de coincidir amb les que llegeix l'AdminController
     * quan l'administrador les modifica des del Panell d'Admin:
     *   · atracon_prob      → % de probabilitat d'Atracón
     *   · sobredosis_prob   → % de probabilitat de Sobredosis de Sucre
     *   · bajon_prob        → % de probabilitat de Bajón de Azúcar
     *
     * La suma total no pot superar el 100%.
     * Valors inicials baixos per a una experiència de joc equilibrada.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'atracon_prob',    'value' => 10],
            ['key' => 'sobredosis_prob', 'value' => 5],
            ['key' => 'bajon_prob',      'value' => 5],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
