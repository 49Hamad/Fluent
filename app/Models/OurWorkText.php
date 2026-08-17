<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OurWorkText extends Model
{
    protected $table = 'our_work_texts';

    protected $fillable = [
        'title',
        'description',
    ];
}
