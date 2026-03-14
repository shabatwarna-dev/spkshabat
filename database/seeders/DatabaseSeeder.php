<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProductionOrder;
use App\Models\ProductionProcess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        $marketing = User::create([
            'name' => 'Admin Marketing',
            'email' => 'marketing@printflow.com',
            'password' => Hash::make('password'),
            'role' => 'marketing',
        ]);

        $produksi = User::create([
            'name' => 'Admin Produksi',
            'email' => 'produksi@printflow.com',
            'password' => Hash::make('password'),
            'role' => 'produksi',
        ]);

        // Create sample SPK
        $spk1 = ProductionOrder::create([
            'nomor_spk' => 'SPK-202603-0001',
            'tanggal_pesan' => '2026-03-01',
            'tanggal_produksi' => '2026-03-03',
            'tanggal_selesai_estimasi' => '2026-03-15',
            'tanggal_kirim' => '2026-03-17',
            'nama_customer' => 'PT. Maju Jaya Abadi',
            'nama_barang' => 'Label Botol Shampoo Premium',
            'keterangan' => 'Full color, laminasi glossy. Urgent!',
            'status' => 'produksi',
            'created_by' => $marketing->id,
        ]);

        $processes1 = [
            ['nama_proses'=>'Design', 'urutan'=>1, 'estimasi_selesai'=>'2026-03-03', 'jumlah_barang'=>1, 'montage'=>'1 Mata', 'ukuran'=>'36x63 cm', 'warna'=>'4 warna', 'estimasi_hasil'=>1, 'satuan'=>'file', 'status'=>'selesai', 'hasil_jadi'=>1, 'tanggal_selesai_aktual'=>'2026-03-03'],
            ['nama_proses'=>'Plat', 'urutan'=>2, 'estimasi_selesai'=>'2026-03-04', 'jumlah_barang'=>4, 'montage'=>'4 Mata', 'ukuran'=>'36x63 cm', 'warna'=>'4 warna', 'estimasi_hasil'=>4, 'satuan'=>'pcs', 'status'=>'selesai', 'hasil_jadi'=>4, 'tanggal_selesai_aktual'=>'2026-03-04'],
            ['nama_proses'=>'Cetak', 'urutan'=>3, 'estimasi_selesai'=>'2026-03-07', 'jumlah_barang'=>3443, 'montage'=>'3 Mata', 'ukuran'=>'36x63 cm', 'warna'=>'4 warna', 'estimasi_hasil'=>10299, 'satuan'=>'lembar', 'status'=>'selesai', 'hasil_jadi'=>10280, 'jumlah_reject'=>19, 'tanggal_selesai_aktual'=>'2026-03-08', 'catatan_produksi'=>'Ada 19 lembar reject warna kurang tajam'],
            ['nama_proses'=>'Laminasi', 'urutan'=>4, 'estimasi_selesai'=>'2026-03-09', 'jumlah_barang'=>10280, 'montage'=>'1 Mata', 'ukuran'=>'36x63 cm', 'warna'=>'-', 'estimasi_hasil'=>10280, 'satuan'=>'lembar', 'status'=>'telat', 'catatan_marketing'=>'Laminasi glossy premium'],
            ['nama_proses'=>'Punch', 'urutan'=>5, 'estimasi_selesai'=>'2026-03-11', 'jumlah_barang'=>10280, 'montage'=>'1 Mata', 'ukuran'=>'36x63 cm', 'warna'=>'-', 'estimasi_hasil'=>10280, 'satuan'=>'pcs', 'status'=>'pending'],
            ['nama_proses'=>'Sortir', 'urutan'=>6, 'estimasi_selesai'=>'2026-03-12', 'jumlah_barang'=>10280, 'montage'=>'-', 'ukuran'=>'-', 'warna'=>'-', 'estimasi_hasil'=>10000, 'satuan'=>'pcs', 'status'=>'pending'],
            ['nama_proses'=>'Packing', 'urutan'=>7, 'estimasi_selesai'=>'2026-03-13', 'jumlah_barang'=>10000, 'montage'=>'-', 'ukuran'=>'-', 'warna'=>'-', 'estimasi_hasil'=>200, 'satuan'=>'paket', 'status'=>'pending', 'catatan_marketing'=>'50 pcs per pack, 200 pack total'],
        ];

        foreach ($processes1 as $p) {
            $spk1->processes()->create($p);
        }

        // Second SPK
        $spk2 = ProductionOrder::create([
            'nomor_spk' => 'SPK-202603-0002',
            'tanggal_pesan' => '2026-03-05',
            'tanggal_produksi' => '2026-03-07',
            'tanggal_selesai_estimasi' => '2026-03-20',
            'tanggal_kirim' => '2026-03-22',
            'nama_customer' => 'CV. Berkah Sejahtera',
            'nama_barang' => 'Dus Makanan Ringan',
            'keterangan' => 'Pisau punch baru, koordinasi dengan bagian punch',
            'status' => 'produksi',
            'created_by' => $marketing->id,
        ]);

        $processes2 = [
            ['nama_proses'=>'Design', 'urutan'=>1, 'estimasi_selesai'=>'2026-03-07', 'jumlah_barang'=>1, 'montage'=>'2 Mata', 'ukuran'=>'50x70 cm', 'warna'=>'5 warna', 'estimasi_hasil'=>1, 'satuan'=>'file', 'status'=>'selesai', 'hasil_jadi'=>1, 'tanggal_selesai_aktual'=>'2026-03-07'],
            ['nama_proses'=>'Pisau Punch', 'urutan'=>2, 'estimasi_selesai'=>'2026-03-09', 'jumlah_barang'=>1, 'montage'=>'2 Mata', 'ukuran'=>'50x70 cm', 'warna'=>'-', 'estimasi_hasil'=>1, 'satuan'=>'set', 'status'=>'proses'],
            ['nama_proses'=>'Potong Bahan', 'urutan'=>3, 'estimasi_selesai'=>'2026-03-10', 'jumlah_barang'=>5000, 'montage'=>'2 Mata', 'ukuran'=>'50x70 cm', 'warna'=>'-', 'estimasi_hasil'=>2500, 'satuan'=>'lembar', 'status'=>'pending'],
            ['nama_proses'=>'Cetak', 'urutan'=>4, 'estimasi_selesai'=>'2026-03-14', 'jumlah_barang'=>2500, 'montage'=>'2 Mata', 'ukuran'=>'50x70 cm', 'warna'=>'5 warna', 'estimasi_hasil'=>5000, 'satuan'=>'lembar', 'status'=>'pending'],
            ['nama_proses'=>'Dedel', 'urutan'=>5, 'estimasi_selesai'=>'2026-03-17', 'jumlah_barang'=>5000, 'montage'=>'-', 'ukuran'=>'-', 'warna'=>'-', 'estimasi_hasil'=>5000, 'satuan'=>'pcs', 'status'=>'pending'],
            ['nama_proses'=>'Packing', 'urutan'=>6, 'estimasi_selesai'=>'2026-03-19', 'jumlah_barang'=>5000, 'montage'=>'-', 'ukuran'=>'-', 'warna'=>'-', 'estimasi_hasil'=>100, 'satuan'=>'paket', 'status'=>'pending', 'catatan_marketing'=>'50 pcs per pack'],
        ];

        foreach ($processes2 as $p) {
            $spk2->processes()->create($p);
        }
    }
}
