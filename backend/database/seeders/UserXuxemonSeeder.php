<?php

/**
 * ============================================================
 * FITXER: database/seeders/UserXuxemonSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Assigna Xuxemons inicials als jugadors de prova per permetre
 *   provar la mecànica d'alimentació, evolució i curació des del
 *   primer moment sense haver de reclamar recompenses diàries.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (filtrat per rol 'player')
 *   → Model: App\Models\Xuxemon (filtrat per mida 'Petit')
 *   → Model: App\Models\UserXuxemon (creació directa del pivot)
 *   → Prerequisit de: cap (és el darrer que necessita xuxemons i usuaris)
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Xuxemon;
use App\Models\UserXuxemon;

class UserXuxemonSeeder extends Seeder
{
    public function run(): void
    {
        $players = User::where('role', 'player')->get();
        // Agafem només Petits perquè és el punt d'entrada del joc:
        // els jugadors els han d'alimentar per evolucionar-los.
        $petits  = Xuxemon::where('size', 'Petit')->get();

        if ($petits->isEmpty()) {
            $this->command->warn('No hi ha Xuxemons de mida Petit. Executa XuxemonSeeder primer.');
            return;
        }

        foreach ($players as $player) {
            // Assignem 2 Xuxemons Petits aleatoris per jugador (sense repetir).
            // min() evita errors si hi ha menys de 2 Petits a la BD.
            $assigned = $petits->random(min(2, $petits->count()));

            foreach ($assigned as $index => $xuxemon) {
                // Al segon Xuxemon li posem una malaltia per permetre provar
                // la mecànica de vacunació sense esperar que emmalalteixi aleatòriament.
                $disease = ($index === 1) ? 'Bajón de Azúcar' : null;

                UserXuxemon::create([
                    'user_id'    => $player->id,
                    'xuxemon_id' => $xuxemon->id,
                    // food_eaten aleatori entre 0 i 4 per simular Xuxemons en diferent
                    // estat d'alimentació, fent les proves més representatives.
                    'food_eaten' => rand(0, 4),
                    'disease'    => $disease,
                ]);
            }
        }
    }
}