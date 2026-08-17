<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = "settings";

    protected $fillable = [
        'headerlogo',
        'location',
        'Address',
        'footerlogo',
        'social_links',
        'color',
        'meta_title',
        'meta_image',
        'meta_keywords',
        'meta_description'
    ];

    protected $casts=[
        'meta_keywords'=>'array',
        'Address'=>'json',
        'social_links'=>'json',
        'color'=>'json',
    ];
}
