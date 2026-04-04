<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use App\Models\ProductionOrder;
use App\Models\ProductionProcess;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Teams ─────────────────────────────────────────────
        $teamDigital = Team::create([
            'name'       => 'Tim Digital',
            'jalur'      => 'digital',
            'warna'      => '#3b82f6',
            'keterangan' => 'Tim produksi jalur digital',
        ]);

        $teamOffset = Team::create([
            'name'       => 'Tim Offset',
            'jalur'      => 'offset',
            'warna'      => '#f59e0b',
            'keterangan' => 'Tim produksi jalur offset',
        ]);

        // ── Core Users ────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Master Admin',
            'email'    => 'admin@shabat.com',
            'password' => bcrypt('password'),
            'role'     => 'master_admin',
        ]);

        $ppicDigital = User::create([
            'name'     => 'PPIC Digital',
            'email'    => 'ppicdigital@shabat.com',
            'password' => bcrypt('password'),
            'role'     => 'ppic',
        ]);

        $koorDigital = User::create([
            'name'     => 'Koor Digital',
            'email'    => 'koordigital@shabat.com',
            'password' => bcrypt('password'),
            'role'     => 'koor',
        ]);

        $ppicOffset = User::create([
            'name'     => 'PPIC Offset',
            'email'    => 'ppicoffset@shabat.com',
            'password' => bcrypt('password'),
            'role'     => 'ppic',
        ]);

        $koorOffset = User::create([
            'name'     => 'Koor Offset',
            'email'    => 'kooroffset@shabat.com',
            'password' => bcrypt('password'),
            'role'     => 'koor',
        ]);

        // ── Operator per proses ───────────────────────────────
        $processes = ProductionProcess::defaultProcesses();
        // ['Design', 'Plat', 'Pisau Punch', 'Potong Bahan', 'Cetak', 'Laminasi', 'Punch', 'Dedel', 'Sortir', 'Packing']

        $operatorEmails = [
            'Design'       => 'designer@shabat.com',
            'Plat'         => 'plat@shabat.com',
            'Pisau Punch'  => 'pisaupunch@shabat.com',
            'Potong Bahan' => 'potong@shabat.com',
            'Cetak'        => 'cetak@shabat.com',
            'Laminasi'     => 'laminasi@shabat.com',
            'Punch'        => 'punch@shabat.com',
            'Dedel'        => 'dedel@shabat.com',
            'Sortir'       => 'sortir@shabat.com',
            'Packing'      => 'packing@shabat.com',
        ];

        $operators = [];
        foreach ($processes as $proses) {
            $slug = strtolower(str_replace([' ', '/'], '', $proses));
            $operators[$proses] = User::create([
                'name'       => 'Operator ' . $proses,
                'email'      => $operatorEmails[$proses],
                'password'   => bcrypt('password'),
                'role'       => 'operator',
                'nama_proses'=> $proses,
            ]);
        }

        // ── Assign tim ────────────────────────────────────────
        // PPIC & Koor
        $teamDigital->users()->attach([$ppicDigital->id, $koorDigital->id]);
        $teamOffset->users()->attach([$ppicOffset->id, $koorOffset->id]);

        // Semua operator masuk ke kedua tim
        $operatorIds = collect($operators)->pluck('id')->toArray();
        $teamDigital->users()->attach($operatorIds);
        $teamOffset->users()->attach($operatorIds);

        // ── 5 SPK Digital dengan semua proses ─────────────────
        $customers = ['PT Maju Bersama', 'CV Sinar Jaya', 'UD Berkah Abadi', 'PT Karya Indah', 'Toko Sejahtera'];

        foreach (range(1, 5) as $i) {
            $order = ProductionOrder::create([
                'team_id'                  => $teamDigital->id,
                'created_by'               => $ppicDigital->id,
                'nomor_spk'                => 'SPK-DIG-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tanggal_pesan'            => now()->subDays(10 - $i),
                'tanggal_produksi'         => now()->subDays(8 - $i),
                'tanggal_selesai_estimasi' => now()->addDays($i * 2),
                'tanggal_kirim'            => now()->addDays($i * 2 + 1),
                'nama_customer'            => $customers[$i - 1],
                'nama_barang'              => 'Produk ' . chr(64 + $i),
                'keterangan'               => 'SPK testing Digital ke-' . $i,
                'status'                   => 'produksi',
            ]);

            foreach ($processes as $urutan => $namaProses) {
                $order->processes()->create([
                    'nama_proses'       => $namaProses,
                    'urutan'            => $urutan + 1,
                    'estimasi_selesai'  => now()->addDays($urutan + 1),
                    'jumlah_barang'     => rand(500, 5000),
                    'satuan'            => 'pcs',
                    'estimasi_hasil'    => rand(490, 4950),
                    'catatan_marketing' => 'Proses ' . $namaProses . ' untuk order ' . $i,
                    'status'            => 'pending',
                ]);
            }
        }

        // ── 5 SPK Offset dengan semua proses ──────────────────
        $customersOffset = ['PT Offset Prima', 'CV Cetak Jaya', 'UD Print Abadi', 'PT Warna Indah', 'Toko Print Mas'];

        foreach (range(1, 5) as $i) {
            $order = ProductionOrder::create([
                'team_id'                  => $teamOffset->id,
                'created_by'               => $ppicOffset->id,
                'nomor_spk'                => 'SPK-OFF-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tanggal_pesan'            => now()->subDays(10 - $i),
                'tanggal_produksi'         => now()->subDays(8 - $i),
                'tanggal_selesai_estimasi' => now()->addDays($i * 2),
                'tanggal_kirim'            => now()->addDays($i * 2 + 1),
                'nama_customer'            => $customersOffset[$i - 1],
                'nama_barang'              => 'Produk Offset ' . chr(64 + $i),
                'keterangan'               => 'SPK testing Offset ke-' . $i,
                'status'                   => 'produksi',
            ]);

            foreach ($processes as $urutan => $namaProses) {
                $order->processes()->create([
                    'nama_proses'       => $namaProses,
                    'urutan'            => $urutan + 1,
                    'estimasi_selesai'  => now()->addDays($urutan + 1),
                    'jumlah_barang'     => rand(500, 5000),
                    'satuan'            => 'pcs',
                    'estimasi_hasil'    => rand(490, 4950),
                    'catatan_marketing' => 'Proses ' . $namaProses . ' untuk order ' . $i,
                    'status'            => 'pending',
                ]);
            }
        }
    }
}