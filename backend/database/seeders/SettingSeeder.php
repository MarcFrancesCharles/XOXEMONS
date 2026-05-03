<?php

/**
 * ============================================================
 * FITXER: database/seeders/SettingSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Inicialitza els paràmetres de configuració global del joc. 
 *   Aquest seeder estableix les probabilitats base per a les 
 *   malalties que poden afectar els Xuxemons en ser alimentats.
 *
 * PARÀMETRES INICIALS:
 *   - atracon_prob: 10% (Bloqueja l'alimentació).
 *   - sobredosis_prob: 5% (Efecte de sobredosi).
 *   - bajon_prob: 5% (Dificulta l'evolució).
 *
 * MAPA DE CONNEXIONS:
 *   → Consultat per XuxemonController durant el 'feed'.
 *   → Modificable per l'administrador via AdminController.
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Executa el seeder de configuracions globals.
     */
    public function run(): void
    {
        // Definim els valors d'inici per a les probabilitats de malaltia.
        $settings = [
            ['key' => 'atracon_prob',    'value' => 10],
            ['key' => 'sobredosis_prob', 'value' => 5],
            ['key' => 'bajon_prob',      'value' => 5],
        ];

        // Fem servir updateOrCreate per evitar duplicats si el seeder s'executa 
        // més d'una vegada.
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}