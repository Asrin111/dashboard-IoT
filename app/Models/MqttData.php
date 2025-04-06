<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqttData extends Model
{
    use HasFactory;

    protected $table = 'mqtt_data';

    protected $fillable = ['topic', 'message'];
}