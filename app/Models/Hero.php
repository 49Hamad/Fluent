<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    protected $table = 'heros';

    protected $fillable = [
        'description',
        'link_3d',
        'button_text',
        'button_link',
        'button_video_link',
        'is_button_video',
    ];
}
