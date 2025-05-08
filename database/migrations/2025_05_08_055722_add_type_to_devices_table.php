<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Cek jika kolom 'type' belum ada, lalu tambahkan
            if (!Schema::hasColumn('devices', 'tipe')) {
                $table->string('tipe')->default('default')->after('device_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Hapus kolom 'type' jika ingin rollback
            $table->dropColumn('tipe');
        });
    }
};