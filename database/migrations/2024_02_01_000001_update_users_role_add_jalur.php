<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update role enum to include all 5 roles
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic_digital','koor_digital','ppic_offset','koor_offset') NOT NULL DEFAULT 'koor_digital'");

        // Add jalur column
        Schema::table('users', function (Blueprint $table) {
            $table->enum('jalur', ['digital', 'offset'])->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jalur');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('marketing','produksi') NOT NULL DEFAULT 'produksi'");
    }
};
