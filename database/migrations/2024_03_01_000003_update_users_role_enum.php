<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah role enum jadi 3 tipe saja
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic','koor') NOT NULL DEFAULT 'koor'");

        // Hapus kolom jalur kalau ada (dari migration sebelumnya)
        if (Schema::hasColumn('users', 'jalur')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('jalur');
            });
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic_digital','koor_digital','ppic_offset','koor_offset') NOT NULL DEFAULT 'koor_digital'");
    }
};
