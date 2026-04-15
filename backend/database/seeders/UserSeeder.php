<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Crea un usuari administrador (robot) i 5 jugadors de prova.
     * El custom_id segueix el format Nom#XXXX (sense espais).
     */
    public function run(): void
    {
        // ── Admin ────────────────────────────────────────────────────────────
        User::create([
            'custom_id' => 'Admin#0000',
            'name'      => 'Admin',
            'surnames'  => 'Sistema',
            'email'     => 'admin@xoxemons.com',
            'password'  => Hash::make('admin'),
            'role'      => 'robot',
        ]);

        // ── Jugadors de prova ────────────────────────────────────────────────
        $players = [
            ['name' => 'Jan',    'surnames' => 'García',   'email' => 'jan@xoxemons.com'],
            ['name' => 'Maria',  'surnames' => 'López',    'email' => 'maria@xoxemons.com'],
            ['name' => 'Pau',    'surnames' => 'Martínez', 'email' => 'pau@xoxemons.com'],
            ['name' => 'Laia',   'surnames' => 'Puig',     'email' => 'laia@xoxemons.com'],
            ['name' => 'Arnau',  'surnames' => 'Serra',    'email' => 'arnau@xoxemons.com'],
        ];

        foreach ($players as $index => $data) {
            $number    = str_pad($index + 1001, 4, '0', STR_PAD_LEFT);
            $cleanName = str_replace(' ', '', $data['name']);
            User::create([
                'custom_id' => "{$cleanName}#{$number}",
                'name'      => $data['name'],
                'surnames'  => $data['surnames'],
                'email'     => $data['email'],
                'password'  => Hash::make('admin'),
                'role'      => 'player',
            ]);
        }
    }
}
