<?php

/**
 * ============================================================
 * FITXER: database/seeders/SettingSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Inicialitza els valors de configuració global del joc.
 *   Estableix les probabilitats de malalties per defecte que
 *   l'administrador pot modificar posteriorment des del panell.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\Setting
 *   → Taula BD: settings
 *   → Llegit per: XuxemonController (feed, sistema d'infecció)
 *   → Sobreescrit per: AdminController (updateSettings)
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Probabilitats inicials equilibrades per a una experiència de joc justa.
        // La suma és 20%, deixant un 80% de probabilitat de no emmalaltir per alimentació.
        // L'administrador pot ajustar-les per fer el joc més o menys difícil.
        $settings = [
            // 10% de possibilitats d'Atracón (bloqueja l'alimentació fins vacunar)
            ['key' => 'atracon_prob',    'value' => 10],
            // 5% de possibilitats de Sobredosis de sucre (efecte desconegut per defecte)
            ['key' => 'sobredosis_prob', 'value' => 5],
            // 5% de possibilitats de Bajón de azúcar (requereix 2 xuxes extra per evolucionar)
            ['key' => 'bajon_prob',      'value' => 5],
        ];

        // updateOrCreate garanteix idempotència: el seeder es pot executar
        // múltiples vegades sense crear duplicats ni perdre configuració personalitzada.
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}