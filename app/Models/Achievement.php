<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $table = 'achievements';

    protected $fillable = [
        'title',
        'description',
        'image',
        'achievements',
    ];

    protected $casts = [
        'achievements' => 'array',
    ];
}
