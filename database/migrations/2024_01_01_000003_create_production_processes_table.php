<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();

            // Diisi Marketing
            $table->string('nama_proses'); // design, plat, cetak, etc.
            $table->integer('urutan')->default(0);
            $table->date('estimasi_selesai')->nullable();
            $table->decimal('jumlah_barang', 12, 2)->nullable();
            $table->string('montage')->nullable(); // e.g. "1 Mata", "2 Mata"
            $table->string('ukuran')->nullable(); // e.g. "36x63 cm"
            $table->string('warna')->nullable(); // e.g. "4 warna"
            $table->decimal('estimasi_hasil', 12, 2)->nullable();
            $table->string('satuan')->nullable(); // pcs, paket, lembar, etc.
            $table->text('catatan_marketing')->nullable();

            // Diisi Produksi
            $table->decimal('hasil_jadi', 12, 2)->nullable();
            $table->decimal('jumlah_reject', 12, 2)->nullable()->default(0);
            $table->date('tanggal_selesai_aktual')->nullable();
            $table->text('catatan_produksi')->nullable();
            $table->string('foto_hasil')->nullable(); // file path

            // Status
            $table->enum('status', ['pending', 'proses', 'selesai', 'telat'])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_processes');
    }
};
