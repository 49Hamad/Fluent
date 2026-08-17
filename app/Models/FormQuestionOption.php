<?php

namespace App\Models;

use App\Models\FormQuestion;
use Illuminate\Database\Eloquent\Model;

class FormQuestionOption extends Model
{
    protected $fillable = ['form_question_id', 'option_text'];

    public function question()
    {
        return $this->belongsTo(FormQuestion::class,'form_question_id');
    }
}
