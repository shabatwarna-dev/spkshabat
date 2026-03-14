<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel log riwayat edit hasil produksi
        Schema::create('process_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edited_by')->constrained('users');

            // Nilai sebelum edit
            $table->decimal('hasil_jadi_before', 12, 2)->nullable();
            $table->decimal('jumlah_reject_before', 12, 2)->nullable();
            $table->date('tanggal_selesai_before')->nullable();
            $table->text('catatan_before')->nullable();
            $table->string('foto_before')->nullable();

            // Nilai sesudah edit
            $table->decimal('hasil_jadi_after', 12, 2)->nullable();
            $table->decimal('jumlah_reject_after', 12, 2)->nullable();
            $table->date('tanggal_selesai_after')->nullable();
            $table->text('catatan_after')->nullable();
            $table->string('foto_after')->nullable();

            $table->text('alasan_edit')->nullable(); // wajib diisi saat edit
            $table->timestamps();
        });

        // Tambah kolom is_edited dan edit_count ke production_processes
        Schema::table('production_processes', function (Blueprint $table) {
            $table->boolean('is_edited')->default(false)->after('foto_hasil');
            $table->integer('edit_count')->default(0)->after('is_edited');
            $table->timestamp('first_input_at')->nullable()->after('edit_count'); // waktu input pertama kali
            $table->foreignId('input_by')->nullable()->constrained('users')->after('first_input_at'); // siapa yang input pertama
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_edit_logs');
        Schema::table('production_processes', function (Blueprint $table) {
            $table->dropColumn(['is_edited', 'edit_count', 'first_input_at', 'input_by']);
        });
    }
};
