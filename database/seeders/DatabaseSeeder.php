<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use App\Models\ProductionOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Teams ─────────────────────────────────────────────
        $teamDigital = Team::firstOrCreate(
            ['name' => 'Tim Digital'],
            ['jalur' => 'digital', 'warna' => '#3b82f6', 'keterangan' => 'Tim produksi jalur digital']
        );

        $teamOffset = Team::firstOrCreate(
            ['name' => 'Tim Offset'],
            ['jalur' => 'offset', 'warna' => '#f59e0b', 'keterangan' => 'Tim produksi jalur offset']
        );

        // ── Users ─────────────────────────────────────────────
        $masterAdmin = User::firstOrCreate(
            ['email' => 'admin@spkshabat.com'],
            ['name' => 'Master Admin', 'password' => Hash::make('password'), 'role' => 'master_admin']
        );

        $ppicDigital = User::firstOrCreate(
            ['email' => 'ppic.digital@spkshabat.com'],
            ['name' => 'PPIC Digital', 'password' => Hash::make('password'), 'role' => 'ppic']
        );

        $koorDigital = User::firstOrCreate(
            ['email' => 'koor.digital@spkshabat.com'],
            ['name' => 'Koor Digital', 'password' => Hash::make('password'), 'role' => 'koor']
        );

        $ppicOffset = User::firstOrCreate(
            ['email' => 'ppic.offset@spkshabat.com'],
            ['name' => 'PPIC Offset', 'password' => Hash::make('password'), 'role' => 'ppic']
        );

        $koorOffset = User::firstOrCreate(
            ['email' => 'koor.offset@spkshabat.com'],
            ['name' => 'Koor Offset', 'password' => Hash::make('password'), 'role' => 'koor']
        );

        // ── Assign users ke tim ───────────────────────────────
        $teamDigital->users()->syncWithoutDetaching([$ppicDigital->id, $koorDigital->id]);
        $teamOffset->users()->syncWithoutDetaching([$ppicOffset->id, $koorOffset->id]);

        // ── Sample SPK Digital ────────────────────────────────
        $orderDigital = ProductionOrder::firstOrCreate(
            ['nomor_spk' => 'SPK-TIM-202601-0001'],
            [
                'tanggal_pesan'            => now(),
                'tanggal_produksi'         => now(),
                'tanggal_selesai_estimasi' => now()->addDays(7),
                'tanggal_kirim'            => now()->addDays(8),
                'nama_customer'            => 'PT. Digital Prima',
                'nama_barang'              => 'Brosur Digital A4',
                'status'                   => 'produksi',
                'team_id'                  => $teamDigital->id,
                'created_by'               => $ppicDigital->id,
            ]
        );

        if ($orderDigital->processes->count() === 0) {
            foreach (['Design', 'Cetak', 'Laminasi', 'Packing'] as $i => $nama) {
                $orderDigital->processes()->create([
                    'nama_proses'      => $nama,
                    'urutan'           => $i + 1,
                    'estimasi_selesai' => now()->addDays($i + 1),
                    'jumlah_barang'    => 1000,
                    'satuan'           => 'pcs',
                    'status'           => 'pending',
                ]);
            }
        }

        // ── Sample SPK Offset ─────────────────────────────────
        $orderOffset = ProductionOrder::firstOrCreate(
            ['nomor_spk' => 'SPK-TIM-202601-0002'],
            [
                'tanggal_pesan'            => now(),
                'tanggal_produksi'         => now(),
                'tanggal_selesai_estimasi' => now()->addDays(7),
                'tanggal_kirim'            => now()->addDays(8),
                'nama_customer'            => 'CV. Offset Jaya',
                'nama_barang'              => 'Label Botol Offset',
                'status'                   => 'produksi',
                'team_id'                  => $teamOffset->id,
                'created_by'               => $ppicOffset->id,
            ]
        );

        if ($orderOffset->processes->count() === 0) {
            foreach (['Plat', 'Cetak', 'Punch', 'Packing'] as $i => $nama) {
                $orderOffset->processes()->create([
                    'nama_proses'      => $nama,
                    'urutan'           => $i + 1,
                    'estimasi_selesai' => now()->addDays($i + 1),
                    'jumlah_barang'    => 5000,
                    'satuan'           => 'pcs',
                    'status'           => 'pending',
                ]);
            }
        }
    }
}