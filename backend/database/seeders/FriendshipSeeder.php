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
        // Recuperem els usuaris de prova creats pel UserSeeder.
        $jan   = User::where('name', 'Jan')->first();
        $maria = User::where('name', 'Maria')->first();
        $pau   = User::where('name', 'Pau')->first();
        $laia  = User::where('name', 'Laia')->first();

        // Verifiquem que els usuaris existeixen abans d'intentar crear vincles.
        if (! $jan || ! $maria || ! $pau || ! $laia) {
            return;
        }

        $friendships = [
            // Amistat acceptada entre Jan i Maria.
            ['user_id' => $jan->id,  'friend_id' => $maria->id, 'status' => 'accepted'],

            // Sol·licitud enviada per Jan a Pau (pendent).
            ['user_id' => $jan->id,  'friend_id' => $pau->id,   'status' => 'pending'],

            // Sol·licitud enviada per Laia a Jan (pendent de Jan).
            ['user_id' => $laia->id, 'friend_id' => $jan->id,   'status' => 'pending'],

            // Amistat acceptada entre Pau i Laia.
            ['user_id' => $pau->id,  'friend_id' => $laia->id,  'status' => 'accepted'],
        ];

        // Creem els registres a la taula 'friendships'.
        foreach ($friendships as $data) {
            Friendship::create($data);
        }
    }
}