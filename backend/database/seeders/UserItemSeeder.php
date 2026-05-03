<?php

/**
 * ============================================================
 * FITXER: database/seeders/UserItemSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Emplena les motxilles dels jugadors de prova amb ítems inicials. 
 *   Aquest seeder garanteix que els jugadors tinguin prou xuxes 
 *   i vacunes per provar el joc sense esperar a les recompenses 
 *   diàries.
 *
 * LÒGICA D'ASSIGNACIÓ:
 *   - Xuxes: S'entreguen 2 tipus diferents (10 unitats cadascun).
 *   - Vacunes: S'entrega 1 tipus (2 unitats) per gestionar malalties.
 *
 * MAPA DE CONNEXIONS:
 *   → Requereix: UserSeeder i ItemSeeder.
 *   → Afecta: Taula pivot 'user_items'.
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;

class UserItemSeeder extends Seeder
{
    /**
     * Executa el seeder d'inventari d'usuaris.
     */
    public function run(): void
    {
        // Obtenim tots els usuaris que no són administradors.
        $players = User::where('role', 'usuari')->get();
        $xuxes   = Item::where('type', 'xuxe')->get();
        $vacunes = Item::where('type', 'vacuna')->get();

        // Verifiquem que el catàleg d'ítems no estigui buit.
        if ($xuxes->isEmpty() || $vacunes->isEmpty()) {
            return;
        }

        foreach ($players as $player) {
            /** @var User $player */

            // Assignem 2 tipus de xuxes aleatòries amb 10 unitats de cadascuna.
            $randomXuxes = $xuxes->random(min(2, $xuxes->count()));
            foreach ($randomXuxes as $xuxe) {
                $player->items()->attach($xuxe->id, ['quantity' => 10]);
            }

            // Assignem 1 tipus de vacuna aleatòria amb 2 unitats.
            $randomVacuna = $vacunes->random(1)->first();
            if ($randomVacuna) {
                $player->items()->attach($randomVacuna->id, ['quantity' => 2]);
            }
        }
    }
}