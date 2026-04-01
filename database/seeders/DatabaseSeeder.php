<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use App\Models\ProductionOrder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Teams
        $teamDigital = Team::create([
            'name' => 'Tim Digital',
            'jalur' => 'digital',
            'warna' => '#3b82f6',
            'keterangan' => 'Tim produksi jalur digital'
        ]);

        $teamOffset = Team::create([
            'name' => 'Tim Offset',
            'jalur' => 'offset',
            'warna' => '#f59e0b',
            'keterangan' => 'Tim produksi jalur offset'
        ]);

        // Users
        $admin = User::create([
            'name' => 'Master Admin',
            'email' => 'admin@shabat.com',
            'password' => bcrypt('password'),
            'role' => 'master_admin'
        ]);

        $ppicDigital = User::create([
            'name' => 'PPIC Digital',
            'email' => 'ppicdigital@shabat.com',
            'password' => bcrypt('password'),
            'role' => 'ppic'
        ]);

        $koorDigital = User::create([
            'name' => 'Koor Digital',
            'email' => 'koordigital@shabat.com',
            'password' => bcrypt('password'),
            'role' => 'koor'
        ]);

        $ppicOffset = User::create([
            'name' => 'PPIC Offset',
            'email' => 'ppicoffset@shabat.com',
            'password' => bcrypt('password'),
            'role' => 'ppic'
        ]);

        $koorOffset = User::create([
            'name' => 'Koor Offset',
            'email' => 'kooroffset@shabat.com',
            'password' => bcrypt('password'),
            'role' => 'koor'
        ]);

        // Assign team
        $teamDigital->users()->attach([$ppicDigital->id, $koorDigital->id]);
        $teamOffset->users()->attach([$ppicOffset->id, $koorOffset->id]);

        // Orders
        ProductionOrder::factory()
            ->count(5)
            ->hasProcesses(4)
            ->create([
                'team_id' => $teamDigital->id,
                'created_by' => $ppicDigital->id,
            ]);

        ProductionOrder::factory()
            ->count(5)
            ->hasProcesses(4)
            ->create([
                'team_id' => $teamOffset->id,
                'created_by' => $ppicOffset->id,
            ]);
    }
}