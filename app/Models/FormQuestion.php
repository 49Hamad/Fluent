<?php

namespace App\Models;

use App\Models\FormType;
use App\Models\FormResponse;
use App\Models\FormQuestionOption;
use Illuminate\Database\Eloquent\Model;

class FormQuestion extends Model
{
    protected $fillable = [
        'form_type_id', 'question_text', 'question_type', 'number_of_stars',
        'type_of_stars', 'is_required'
    ];

    public function form()
    {
        return $this->belongsTo(FormType::class);
    }

    public function options()
    {
        return $this->hasMany(FormQuestionOption::class);
    }

    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }
}
