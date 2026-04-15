<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Friendship;

class FriendshipSeeder extends Seeder
{
    /**
     * Crea relacions d'amistat de prova entre els jugadors.
     *
     * Escenaris coberts:
     *   · Jan  ↔ Maria  → acceptada  (amistat establerta)
     *   · Jan  →  Pau   → pendent    (sol·licitud enviada)
     *   · Laia →  Jan   → pendent    (sol·licitud rebuda per Jan)
     *   · Pau  ↔ Laia   → acceptada  (amistat entre dos jugadors)
     *
     * Nota: la taula friendships té una restricció unique(['user_id','friend_id']),
     * per tant cada parella es registra una sola vegada (el que inicia la sol·licitud
     * és el user_id, l'altre és el friend_id).
     */
    public function run(): void
    {
        $jan   = User::where('name', 'Jan')->first();
        $maria = User::where('name', 'Maria')->first();
        $pau   = User::where('name', 'Pau')->first();
        $laia  = User::where('name', 'Laia')->first();

        if (! $jan || ! $maria || ! $pau || ! $laia) {
            $this->command->warn('No es trobaren tots els jugadors necessaris. Executa UserSeeder primer.');
            return;
        }

        $friendships = [
            // Jan ↔ Maria (acceptada)
            ['user_id' => $jan->id,  'friend_id' => $maria->id, 'status' => 'accepted'],
            // Jan → Pau (pendent)
            ['user_id' => $jan->id,  'friend_id' => $pau->id,   'status' => 'pending'],
            // Laia → Jan (pendent — apareixerà com a sol·licitud rebuda per Jan)
            ['user_id' => $laia->id, 'friend_id' => $jan->id,   'status' => 'pending'],
            // Pau ↔ Laia (acceptada)
            ['user_id' => $pau->id,  'friend_id' => $laia->id,  'status' => 'accepted'],
        ];

        foreach ($friendships as $data) {
            Friendship::create($data);
        }
    }
}
