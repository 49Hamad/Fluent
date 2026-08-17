<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyChooseUs extends Model
{
    protected $table = 'why_choose_us';

    protected $fillable = [
        'main_title',
        'sub_title',
        'description',
        'button_text',
        'button_link',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];
}
