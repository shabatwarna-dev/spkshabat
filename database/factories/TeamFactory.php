<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        $types = [
            [
                'name' => 'Tim Digital',
                'jalur' => 'digital',
                'warna' => '#3b82f6',
                'keterangan' => 'Tim produksi jalur digital'
            ],
            [
                'name' => 'Tim Offset',
                'jalur' => 'offset',
                'warna' => '#f59e0b',
                'keterangan' => 'Tim produksi jalur offset'
            ],
        ];

        return $this->faker->randomElement($types);
    }
}