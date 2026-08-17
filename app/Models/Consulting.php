<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulting extends Model
{
    protected $table = 'consulting';

    protected $fillable = [
        'title',
        'description',
        'button_text',
        'button_link',
        'is_active',
    ];
}
