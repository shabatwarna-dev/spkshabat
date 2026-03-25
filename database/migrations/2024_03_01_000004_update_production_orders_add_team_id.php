<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Hapus kolom jalur kalau ada
            if (Schema::hasColumn('production_orders', 'jalur')) {
                $table->dropColumn('jalur');
            }
            // Tambah team_id
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null')->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
            $table->enum('jalur', ['digital', 'offset'])->default('digital');
        });
    }
};
