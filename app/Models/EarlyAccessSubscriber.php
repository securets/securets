<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyAccessSubscriber extends Model
{
    protected $fillable = [
        'email',
        'role',
        'team_size',
        'use_case',
        'ip_address',
    ];
}
