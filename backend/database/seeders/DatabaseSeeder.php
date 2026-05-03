<?php

/**
 * ============================================================
 * FITXER: database/seeders/DatabaseSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest fitxer és l'orquestrador principal que defineix l'ordre 
 *   en què s'han de carregar les dades inicials a la base de dades. 
 *   L'ordre és fonamental per garantir que no hi hagi errors de 
 *   claus foranes (foreign keys).
 *
 * JERARQUIA DE CÀRREGA:
 *   1. Entitats Independents (Usuaris, Espècies base, Ítems).
 *   2. Configuracions Globals.
 *   3. Relacions i Assignacions (Propietat de criatures, motxilla, amics).
 *
 * EXECUCIÓ:
 *   → php artisan db:seed
 *   → php artisan migrate:fresh --seed (recomanat per a reset complet)
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Executa els seeders de l'aplicació.
     */
    public function run(): void
    {
        $this->call([
            // ─────────────────────────────────────────────────────────
            // ENTITATS BASE
            // ─────────────────────────────────────────────────────────
            
            // Creem els jugadors i administradors inicials.
            UserSeeder::class,

            // Carreguem les 18 definicions de Xuxemons al catàleg mestre.
            XuxemonSeeder::class,

            // Carreguem el llistat de xuxes i vacunes disponibles al joc.
            ItemSeeder::class,

            // ─────────────────────────────────────────────────────────
            // CONFIGURACIONS
            // ─────────────────────────────────────────────────────────
            
            // Establim les probabilitats globals de malaltia del sistema.
            SettingSeeder::class,

            // ─────────────────────────────────────────────────────────
            // RELACIONS I DADES DINÀMIQUES
            // ─────────────────────────────────────────────────────────
            
            // Assignem criatures concretes als usuaris creats anteriorment.
            UserXuxemonSeeder::class,

            // Donem ítems inicials a les motxilles dels jugadors.
            UserItemSeeder::class,

            // Establim vincles socials i sol·licituds d'amistat de prova.
            FriendshipSeeder::class,
        ]);
    }
}