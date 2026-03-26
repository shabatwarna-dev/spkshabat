<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionProcessFactory extends Factory
{
    public function definition(): array
    {
        $steps = ['Design', 'Cetak', 'Laminasi', 'Packing'];

        return [
            'nama_proses' => $this->faker->randomElement($steps),
            'urutan' => $this->faker->numberBetween(1, 5),
            'estimasi_selesai' => now()->addDays(rand(1,5)),
            'jumlah_barang' => $this->faker->numberBetween(100, 5000),
            'satuan' => 'pcs',
            'status' => 'pending',
        ];
    }
}