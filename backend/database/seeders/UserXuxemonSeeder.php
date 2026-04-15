<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Xuxemon;
use App\Models\UserXuxemon;

class UserXuxemonSeeder extends Seeder
{
    /**
     * Assigna Xuxemons de prova als jugadors.
     *
     * Cada jugador (rol 'player') rep:
     *   · 2 Xuxemons aleatoris de mida 'Petit'  (punt de partida del joc)
     *
     * Un Xuxemon de prova té una malaltia per comprovar la funcionalitat
     * de curar des del frontend.
     */
    public function run(): void
    {
        $players = User::where('role', 'player')->get();
        $petits  = Xuxemon::where('size', 'Petit')->get();

        if ($petits->isEmpty()) {
            $this->command->warn('No hi ha Xuxemons de mida Petit. Executa XuxemonSeeder primer.');
            return;
        }

        foreach ($players as $player) {
            // Agafem 2 Xuxemons Petits aleatoris (sense repetir)
            $assigned = $petits->random(min(2, $petits->count()));

            foreach ($assigned as $index => $xuxemon) {
                // Al segon Xuxemon li posem una malaltia per poder testejar
                $disease = ($index === 1) ? 'Bajón de Azúcar' : null;

                UserXuxemon::create([
                    'user_id'    => $player->id,
                    'xuxemon_id' => $xuxemon->id,
                    'food_eaten' => rand(0, 4),
                    'disease'    => $disease,
                ]);
            }
        }
    }
}
