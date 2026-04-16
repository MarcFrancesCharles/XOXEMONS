<?php

/**
 * ============================================================
 * FITXER: database/seeders/UserItemSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Dona ítems inicials als jugadors de prova: xuxes per alimentar
 *   Xuxemons i vacunes per curar malalties. Permet provar tot el
 *   flux del joc immediatament sense dependre de la recompensa diària.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (filtrat per rol 'player')
 *   → Model: App\Models\Item (filtrat per tipus)
 *   → Taula pivot: user_items (via $player->items()->attach())
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;

class UserItemSeeder extends Seeder
{
    public function run(): void
    {
        $players = User::where('role', 'player')->get();
        $xuxes   = Item::where('type', 'xuxe')->get();
        $vacunes = Item::where('type', 'vacuna')->get();

        if ($xuxes->isEmpty() || $vacunes->isEmpty()) {
            $this->command->warn('No hi ha ítems. Executa ItemSeeder primer.');
            return;
        }

        foreach ($players as $player) {
            /** @var User $player */

            // Donem 2 tipus de xuxes aleatòries × 10 unitats cadascuna.
            // 10 unitats = 2 espais de motxilla (apilament de 5 en 5) → marge ampli per provar.
            $randomXuxes = $xuxes->random(min(2, $xuxes->count()));
            foreach ($randomXuxes as $xuxe) {
                $player->items()->attach($xuxe->id, ['quantity' => 10]);
            }

            // Donem 1 vacuna aleatòria × 2 unitats per tenir un marge de prova.
            // El jugador pot necessitar curar el Xuxemon malalt que li assigna UserXuxemonSeeder.
            $randomVacuna = $vacunes->random(1)->first();
            $player->items()->attach($randomVacuna->id, ['quantity' => 2]);
        }
    }
}