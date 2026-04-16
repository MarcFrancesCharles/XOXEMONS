<?php

/**
 * ============================================================
 * FITXER: database/seeders/FriendshipSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Crea relacions d'amistat de prova entre els jugadors de
 *   l'entorn de desenvolupament. Cobreix els tres estats possibles
 *   d'una relació: acceptada, pendent enviat i pendent rebut,
 *   per permetre provar totes les pantalles d'amistat del frontend.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (cerca per nom per obtenir IDs)
 *   → Model: App\Models\Friendship (creació de les relacions)
 *   → Depèn de: UserSeeder (Jan, Maria, Pau i Laia han d'existir)
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Friendship;

class FriendshipSeeder extends Seeder
{
    public function run(): void
    {
        // Cerquem els usuaris per nom per obtenir els seus IDs reals de la BD.
        // No usem IDs hardcodejats per robustesa: si el seeder s'executa en un
        // entorn diferent, els IDs poden variar.
        $jan   = User::where('name', 'Jan')->first();
        $maria = User::where('name', 'Maria')->first();
        $pau   = User::where('name', 'Pau')->first();
        $laia  = User::where('name', 'Laia')->first();

        if (! $jan || ! $maria || ! $pau || ! $laia) {
            $this->command->warn('No es trobaren tots els jugadors necessaris. Executa UserSeeder primer.');
            return;
        }

        $friendships = [
            // Amistat acceptada: Jan ↔ Maria → permet provar Xat i Batalla entre ells.
            ['user_id' => $jan->id,  'friend_id' => $maria->id, 'status' => 'accepted'],

            // Sol·licitud pendent enviada: Jan → Pau → Jan pot veure-la a "enviades".
            ['user_id' => $jan->id,  'friend_id' => $pau->id,   'status' => 'pending'],

            // Sol·licitud pendent rebuda: Laia → Jan → Jan la veu a "per acceptar".
            ['user_id' => $laia->id, 'friend_id' => $jan->id,   'status' => 'pending'],

            // Amistat acceptada: Pau ↔ Laia → dos jugadors que Jan no coneix.
            ['user_id' => $pau->id,  'friend_id' => $laia->id,  'status' => 'accepted'],
        ];

        foreach ($friendships as $data) {
            Friendship::create($data);
        }
    }
}