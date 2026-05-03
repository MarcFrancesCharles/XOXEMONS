<?php

/**
 * ============================================================
 * FITXER: database/seeders/UserSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest seeder pobla la taula 'users' amb un conjunt inicial 
 *   d'usuaris. Crea tant el perfil d'administrador ('robot') 
 *   com diversos jugadors de prova per poder testejar les 
 *   funcionalitats socials i de combat des del primer moment.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User.
 *   → Prerequisit per a: Tots els seeders de relacions (Xuxemons, Ítems, Amics).
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Executa el seeder d'usuaris.
     */
    public function run(): void
    {
        // ── ADMINISTRADOR (ROBOT) ───────────────────────────────
        
        // El perfil 'robot' té accés al panell d'administració d'Angular 
        // per injectar ítems i modificar la dificultat del joc.
        User::create([
            'custom_id' => 'Admin#0000',
            'name'      => 'Admin',
            'surnames'  => 'Sistema',
            'email'     => 'admin@xoxemons.com',
            // Contrasenya de desenvolupament: admin
            'password'  => Hash::make('admin'),
            'role'      => 'robot',
        ]);

        // ── JUGADORS DE PROVA ──────────────────────────────────
        
        $players = [
            ['name' => 'Jan',   'surnames' => 'García',   'email' => 'jan@xoxemons.com'],
            ['name' => 'Maria', 'surnames' => 'López',    'email' => 'maria@xoxemons.com'],
            ['name' => 'Pau',   'surnames' => 'Martínez', 'email' => 'pau@xoxemons.com'],
            ['name' => 'Laia',  'surnames' => 'Puig',     'email' => 'laia@xoxemons.com'],
            ['name' => 'Arnau', 'surnames' => 'Serra',    'email' => 'arnau@xoxemons.com'],
        ];

        foreach ($players as $index => $data) {
            // Generem un identificador únic Nom#XXXX per a cada jugador.
            // Els números comencen a partir de 1001.
            $number    = str_pad($index + 1001, 4, '0', STR_PAD_LEFT);
            $cleanName = str_replace(' ', '', $data['name']);

            User::create([
                'custom_id' => "{$cleanName}#{$number}",
                'name'      => $data['name'],
                'surnames'  => $data['surnames'],
                'email'     => $data['email'],
                // Tots els jugadors de prova tenen la mateixa contrasenya per facilitar els tests.
                'password'  => Hash::make('admin'),
                'role'      => 'usuari',
            ]);
        }
    }
}