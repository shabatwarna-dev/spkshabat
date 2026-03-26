<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable strict mode sementara agar warning tidak jadi error
        DB::statement("SET sql_mode = ''");

        // Mapping role lama ke role baru
        DB::statement("UPDATE users SET role = 'koor_digital' WHERE role IN ('marketing', 'produksi')");

        // Ubah ENUM ke 5 role baru
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic_digital','koor_digital','ppic_offset','koor_offset') NOT NULL DEFAULT 'koor_digital'");

        // Add jalur column
        if (!Schema::hasColumn('users', 'jalur')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('jalur', ['digital', 'offset'])->nullable()->after('role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'jalur')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('jalur');
            });
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('marketing','produksi') NOT NULL DEFAULT 'produksi'");
    }
};