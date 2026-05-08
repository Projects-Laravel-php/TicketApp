<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAssignment extends Model
{
    protected $fillable = [
        'device_id',
        'user_id',
        'assigned_at'
    ];
}