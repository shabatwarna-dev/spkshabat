<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mapping role lama ke role baru agar tidak truncated
        DB::statement("UPDATE users SET role = 'koor' WHERE role IN ('koor_digital', 'koor_offset')");
        DB::statement("UPDATE users SET role = 'ppic' WHERE role IN ('ppic_digital', 'ppic_offset')");

        // 2. Ubah role enum jadi 3 tipe saja
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic','koor') NOT NULL DEFAULT 'koor'");

        // 3. Hapus kolom jalur kalau ada (dari migration sebelumnya)
        if (Schema::hasColumn('users', 'jalur')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('jalur');
            });
        }
    }

    public function down(): void
    {
        // 1. Tambah kembali kolom jalur
        if (!Schema::hasColumn('users', 'jalur')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('jalur', ['digital', 'offset'])->nullable()->after('role');
            });
        }

        // 2. Mapping role ke versi sebelumnya (default ke digital)
        DB::statement("UPDATE users SET role = 'koor_digital' WHERE role = 'koor'");
        DB::statement("UPDATE users SET role = 'ppic_digital' WHERE role = 'ppic'");

        // 3. Kembalikan ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic_digital','koor_digital','ppic_offset','koor_offset') NOT NULL DEFAULT 'koor_digital'");
    }
};