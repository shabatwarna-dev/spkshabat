<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spk')->unique(); // e.g. SPK-2026-0001
            $table->date('tanggal_pesan');
            $table->date('tanggal_produksi')->nullable();
            $table->date('tanggal_selesai_estimasi')->nullable();
            $table->date('tanggal_selesai_aktual')->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->string('nama_customer');
            $table->string('nama_barang');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'produksi', 'selesai', 'kirim', 'batal'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
