<?php

/**
 * ============================================================
 * FITXER: database/seeders/UserXuxemonSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Assigna criatures inicials als jugadors de prova. Aquest 
 *   seeder garanteix que els jugadors tinguin Xuxemons en la 
 *   seva col·lecció des del primer moment per provar les 
 *   mecàniques d'alimentació, evolució i combat.
 *
 * DETALLS DE L'ASSIGNACIÓ:
 *   - Espècie: S'entreguen sempre Xuxemons de mida 'Petit'.
 *   - Estat: Alguns Xuxemons s'entreguen ja amb una malaltia 
 *     per provar la curació.
 *   - Alimentació: S'assigna un valor de 'food_eaten' aleatori.
 *
 * MAPA DE CONNEXIONS:
 *   → Requereix: UserSeeder i XuxemonSeeder.
 *   → Afecta: Taula pivot 'user_xuxemons'.
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Xuxemon;
use App\Models\UserXuxemon;

class UserXuxemonSeeder extends Seeder
{
    /**
     * Executa el seeder de Xuxemons d'usuari.
     */
    public function run(): void
    {
        // Obtenim tots els usuaris i les espècies base de mida Petit.
        $players = User::all();
        $petits  = Xuxemon::where('size', 'Petit')->get();

        if ($petits->isEmpty()) {
            return;
        }

        foreach ($players as $player) {
            // Assignem 2 Xuxemons Petits aleatoris a cada jugador.
            $assigned = $petits->random(min(2, $petits->count()));

            foreach ($assigned as $index => $xuxemon) {
                // Al segon Xuxemon li assignem una malaltia 'Bajón de Azúcar' 
                // per facilitar els tests de vacunació.
                $disease = ($index === 1) ? 'Bajón de Azúcar' : null;

                UserXuxemon::create([
                    'user_id'    => $player->id,
                    'xuxemon_id' => $xuxemon->id,
                    // Simulem un estat d'alimentació parcial (0 a 4 xuxes menjades).
                    'food_eaten' => rand(0, 4),
                    'disease'    => $disease,
                ]);
            }
        }
    }
}