<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ============================================================
 * FITXER: database/factories/UserFactory.php
 * ============================================================
 * ROL:
 *   Defineix el patró per a la generació automàtica de dades 
 *   aleatòries per al model User. S'utilitza principalment 
 *   durant les proves (tests) i el seeding inicial.
 * ============================================================
 */

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Contrasenya pre-encriptada utilitzada per la factory per estalviar temps.
     */
    protected static ?string $password;

    /**
     * Defineix l'estat per defecte del model User.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generem un nom i cognom aleatoris.
        $name = fake()->firstName();
        $surnames = fake()->lastName();

        return [
            // Identificador personalitzat format per Nom#XXXX.
            'custom_id' => $name . '#' . fake()->numerify('####'),
            'name' => $name,
            'surnames' => $surnames,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Rol per defecte: usuari normal.
            'role' => 'usuari',
            'last_daily_reward' => null,
        ];
    }

    /**
     * Indica que l'adreça de correu de l'usuari no ha estat verificada.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Estat especial per crear usuaris administradors ('robot').
     */
    public function robot(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'robot',
        ]);
    }
}
