<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OurWork extends Model
{
    protected $table = 'our_works';

    protected $fillable = [
        'title',
        'link',
        'image',
    ];
}
