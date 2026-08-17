<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceText extends Model
{
    protected $table = 'service_texts';

    protected $fillable = [
        'title',
        'description',
    ];
}
