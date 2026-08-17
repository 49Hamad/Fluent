<?php

namespace App\Models;

use App\Models\FormSection;
use App\Models\FormQuestion;
use App\Models\FormEvaluation;
use Illuminate\Database\Eloquent\Model;

class FormType extends Model
{
    protected $table = 'forms';
    protected $fillable = ['form_section_id', 'title', 'description'];

    public function formSection()
    {
        return $this->belongsTo(FormSection::class,'form_section_id');
    }

    public function questions()
    {
        return $this->hasMany(FormQuestion::class);
    }

    public function evaluations()
    {
        return $this->hasMany(FormEvaluation::class);
    }

}
