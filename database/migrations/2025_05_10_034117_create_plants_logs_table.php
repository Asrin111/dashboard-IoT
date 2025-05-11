<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plants_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->float('suhu');
            $table->float('kelembapan');
            $table->integer('moisture');
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants_logs');
    }
};