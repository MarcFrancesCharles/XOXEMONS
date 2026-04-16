<?php

/**
 * ============================================================
 * FITXER: database/seeders/DatabaseSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Orquestra l'ordre d'execució de tots els seeders del projecte.
 *   L'ordre és CRÍTIC per respectar les dependències de claus
 *   foranes: no es pot assignar un Xuxemon a un usuari si l'usuari
 *   o el Xuxemon no existeix encara.
 *
 * MAPA DE CONNEXIONS:
 *   → Crida a: UserSeeder → XuxemonSeeder → ItemSeeder → SettingSeeder
 *              → UserXuxemonSeeder → UserItemSeeder → FriendshipSeeder
 *   → Executat amb: php artisan db:seed
 *                   php artisan migrate:fresh --seed
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Primer els usuaris: la resta de seeders els necessiten com a base.
            UserSeeder::class,

            // 2. El catàleg de Xuxemons (dades base del joc, sense FK de jugadors).
            XuxemonSeeder::class,

            // 3. El catàleg d'ítems (dades base del joc, sense FK de jugadors).
            ItemSeeder::class,

            // 4. Configuració global de malalties (independent de jugadors i ítems).
            SettingSeeder::class,

            // 5. Assignació de Xuxemons als jugadors: requereix UserSeeder + XuxemonSeeder.
            UserXuxemonSeeder::class,

            // 6. Assignació d'ítems als jugadors: requereix UserSeeder + ItemSeeder.
            UserItemSeeder::class,

            // 7. Relacions d'amistat: requereix UserSeeder (necessita els IDs dels jugadors).
            FriendshipSeeder::class,
        ]);
    }
}