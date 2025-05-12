<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingLog extends Model
{
    protected $fillable = ['device_id', 'slots','direction'];
}