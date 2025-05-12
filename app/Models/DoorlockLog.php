<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoorlockLog extends Model
{
    protected $fillable = ['device_id', 'akses'];
}