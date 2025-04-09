<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('mqtt_data', function (Blueprint $table) {
        $table->string('device_type')->nullable()->after('message');
    });
}

public function down()
{
    Schema::table('mqtt_data', function (Blueprint $table) {
        $table->dropColumn('device_type');
    });
}

};