<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantsLog extends Model
{
    protected $fillable = ['device_id', 'suhu', 'kelembapan', 'moisture', 'logged_at'];
}