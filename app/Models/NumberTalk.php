<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberTalk extends Model
{
    protected $table = 'number_talks';

    protected $fillable = [
        'title',
        'description',
        'counters',
    ];

    protected $casts = [
        'counters' => 'array',
    ];
}
