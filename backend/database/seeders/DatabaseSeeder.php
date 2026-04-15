<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Executa tots els seeders del projecte XOXEMONS en ordre de dependències:
     *
     *  1. UserSeeder         → usuaris (admin + jugadors de prova)
     *  2. XuxemonSeeder      → catàleg de criatures (18 Xuxemons)
     *  3. ItemSeeder         → catàleg d'ítems (6 xuxes + 3 vacunes)
     *  4. SettingSeeder      → configuració global de malalties
     *  5. UserXuxemonSeeder  → assignació de Xuxemons als jugadors  [requereix 1+2]
     *  6. UserItemSeeder     → assignació d'ítems als jugadors       [requereix 1+3]
     *  7. FriendshipSeeder   → relacions d'amistat de prova          [requereix 1]
     *
     * Per executar:
     *   php artisan db:seed
     *
     * Per fer un reset complet i tornar a sembrar:
     *   php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            XuxemonSeeder::class,
            ItemSeeder::class,
            SettingSeeder::class,
            UserXuxemonSeeder::class,
            UserItemSeeder::class,
            FriendshipSeeder::class,
        ]);
    }
}
