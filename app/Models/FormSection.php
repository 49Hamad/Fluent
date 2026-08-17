<?php

namespace App\Models;

use App\Models\FormType;
use Illuminate\Database\Eloquent\Model;

class FormSection extends Model
{
    protected $fillable = ['name', 'is_active'];


    public function FormType()
    {
        return $this->hasMany(FormType::class);
    }
}
