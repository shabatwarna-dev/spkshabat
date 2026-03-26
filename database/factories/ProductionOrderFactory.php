<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Team;
use App\Models\User;

class ProductionOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nomor_spk' => 'SPK-' . strtoupper($this->faker->bothify('???')) . '-' . $this->faker->numberBetween(1000,9999),
            'tanggal_pesan' => now(),
            'tanggal_produksi' => now(),
            'tanggal_selesai_estimasi' => now()->addDays(7),
            'tanggal_kirim' => now()->addDays(8),
            'nama_customer' => $this->faker->company(),
            'nama_barang' => $this->faker->word(),
            'status' => 'produksi',
            'team_id' => null,
            'created_by' => null,
        ];
    }
}