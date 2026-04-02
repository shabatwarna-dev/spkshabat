<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah estimasi_selesai di production_processes dari date ke datetime
        DB::statement("ALTER TABLE production_processes MODIFY COLUMN estimasi_selesai DATETIME NULL");

        // Ubah tanggal_selesai_aktual juga ke datetime untuk konsistensi
        DB::statement("ALTER TABLE production_processes MODIFY COLUMN tanggal_selesai_aktual DATETIME NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE production_processes MODIFY COLUMN estimasi_selesai DATE NULL");
        DB::statement("ALTER TABLE production_processes MODIFY COLUMN tanggal_selesai_aktual DATE NULL");
    }
};
