<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraService extends Model
{
    protected $table = 'extra_services';

    protected $fillable = [
        'name',
        'is_active',
    ];
}
