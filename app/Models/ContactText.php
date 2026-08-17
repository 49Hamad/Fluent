<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactText extends Model
{
    protected $table = 'contact_texts';

    protected $fillable = [
        'title',
        'description',
    ];
}
