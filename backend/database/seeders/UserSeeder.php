<?php

/**
 * ============================================================
 * FITXER: database/seeders/UserSeeder.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Crea l'usuari administrador i 5 jugadors de prova amb dades
 *   predefinides. Proporciona un entorn de proves funcional
 *   des del primer 'migrate:fresh --seed'.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User
 *   → Taula BD: users
 *   → Prerequisit de: UserXuxemonSeeder, UserItemSeeder, FriendshipSeeder
 * ============================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────
        // El primer usuari del sistema rep el rol 'robot' (administrador).
        // custom_id fix 'Admin#0000' per facilitar el login durant el desenvolupament.
        User::create([
            'custom_id' => 'Admin#0000',
            'name'      => 'Admin',
            'surnames'  => 'Sistema',
            'email'     => 'admin@xoxemons.com',
            // La contrasenya 'admin' és per a desenvolupament. En producció
            // es canviaria per una de segura a través del panell o via .env.
            'password'  => Hash::make('admin'),
            'role'      => 'robot',
        ]);

        // ── Jugadors de prova ──────────────────────────────────
        $players = [
            ['name' => 'Jan',   'surnames' => 'García',   'email' => 'jan@xoxemons.com'],
            ['name' => 'Maria', 'surnames' => 'López',    'email' => 'maria@xoxemons.com'],
            ['name' => 'Pau',   'surnames' => 'Martínez', 'email' => 'pau@xoxemons.com'],
            ['name' => 'Laia',  'surnames' => 'Puig',     'email' => 'laia@xoxemons.com'],
            ['name' => 'Arnau', 'surnames' => 'Serra',    'email' => 'arnau@xoxemons.com'],
        ];

        foreach ($players as $index => $data) {
            // Generem un número de 4 xifres seqüencial per garantir que
            // cada custom_id és únic sense dependre d'aleatorietat.
            // Comencem a 1001 per separar-nos visualment de l'admin (0000).
            $number    = str_pad($index + 1001, 4, '0', STR_PAD_LEFT);
            $cleanName = str_replace(' ', '', $data['name']);

            User::create([
                'custom_id' => "{$cleanName}#{$number}",
                'name'      => $data['name'],
                'surnames'  => $data['surnames'],
                'email'     => $data['email'],
                'password'  => Hash::make('admin'), // Contrasenya uniforme per a proves
                'role'      => 'player',
            ]);
        }
    }
}