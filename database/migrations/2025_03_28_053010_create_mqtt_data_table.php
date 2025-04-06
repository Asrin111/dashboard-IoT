<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('mqtt_data', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->text('message');
            $table->timestamps(); // Untuk created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('mqtt_data');
    }
};