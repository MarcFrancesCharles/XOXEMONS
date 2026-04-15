<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;

class UserItemSeeder extends Seeder
{
    /**
     * Assigna ítems inicials de prova a cada jugador.
     *
     * Cada jugador rep:
     *   · 10 unitats de 2 xuxes aleatòries (per poder alimentar Xuxemons)
     *   · 2 vacunes (una de cada tipus de malaltia, mínimament)
     *
     * Això permet provar l'inventari, l'alimentació i la curació sense
     * haver de reclamar recompenses diàries.
     */
    public function run(): void
    {
        $players  = User::where('role', 'player')->get();
        $xuxes    = Item::where('type', 'xuxe')->get();
        $vacunes  = Item::where('type', 'vacuna')->get();

        if ($xuxes->isEmpty() || $vacunes->isEmpty()) {
            $this->command->warn('No hi ha ítems. Executa ItemSeeder primer.');
            return;
        }

        foreach ($players as $player) {
            /** @var User $player */

            // 2 xuxes aleatòries × 10 unitats cadascuna
            $randomXuxes = $xuxes->random(min(2, $xuxes->count()));
            foreach ($randomXuxes as $xuxe) {
                $player->items()->attach($xuxe->id, ['quantity' => 10]);
            }

            // 1 vacuna aleatòria × 2 unitats
            $randomVacuna = $vacunes->random(1)->first();
            $player->items()->attach($randomVacuna->id, ['quantity' => 2]);
        }
    }
}
