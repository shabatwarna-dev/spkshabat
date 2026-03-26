<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => $this->faker->randomElement(['master_admin', 'ppic', 'koor']),
        ];
    }

    // Optional state biar gampang
    public function masterAdmin()
    {
        return $this->state([
            'role' => 'master_admin'
        ]);
    }

    public function ppic()
    {
        return $this->state([
            'role' => 'ppic'
        ]);
    }

    public function koor()
    {
        return $this->state([
            'role' => 'koor'
        ]);
    }
}