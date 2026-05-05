<?php

/**
 * ============================================================
 * FITXER: database/seeders/FriendshipSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Crea relacions d'amistat de prova entre els jugadors. Aquest 
 *   seeder és útil per verificar que la llista d'amics, les 
 *   sol·licituds pendents i el xat funcionen correctament a 
 *   la interfície d'Angular.
 *
 * ESTATS DE PROVA:
 *   - accepted: Amistat formal (permet xat i batalla).
 *   - pending: Sol·licitud pendent d'aprovació.
 *
 * MAPA DE CONNEXIONS:
 *   → Depèn de: UserSeeder (per tenir usuaris a qui vincular).
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Friendship;

class FriendshipSeeder extends Seeder
{
    /**
     * Executa el seeder de relacions d'amistat.
     */
    public function run(): void
    {
        // Obtenim tots els usuaris
        $users = User::all();

        // Creem amistat entre tots els usuaris
        for ($i = 0; $i < count($users); $i++) {
            for ($j = $i + 1; $j < count($users); $j++) {
                Friendship::create([
                    'user_id'   => $users[$i]->id,
                    'friend_id' => $users[$j]->id,
                    'status'    => 'accepted',
                ]);
            }
        }
    }
}