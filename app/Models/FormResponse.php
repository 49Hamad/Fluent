<?php

namespace App\Models;

use App\Models\FormQuestion;
use App\Models\FormEvaluation;
use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    protected $fillable = [
        'form_evaluation_id', 'form_question_id', 'response_type',
        'response_text', 'response_options', 'rating_value',
        'type_of_stars', 'response_image'
    ];

    protected $casts = [
        'response_options' => 'array'
    ];

    public function evaluation()
    {
        return $this->belongsTo(FormEvaluation::class,'form_evaluation_id');
    }

    public function question()
    {
        return $this->belongsTo(FormQuestion::class,'form_question_id');
    }
}
