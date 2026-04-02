<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom tipe di production_orders
        Schema::table('production_orders', function (Blueprint $table) {
            $table->enum('tipe', ['general', 'corporate'])->default('general')->after('status');
        });

        // Update role enum di users untuk tambah 'operator'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic','koor','operator') NOT NULL DEFAULT 'koor'");

        // Tambah kolom nama_proses di users (untuk operator)
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_proses')->nullable()->after('role')
                  ->comment('Khusus operator: nama proses yang bisa diinput');
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master_admin','ppic','koor') NOT NULL DEFAULT 'koor'");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nama_proses');
        });
    }
};
