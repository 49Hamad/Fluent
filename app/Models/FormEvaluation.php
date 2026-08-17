<?php

namespace App\Models;

use App\Models\FormType;
use App\Models\FormResponse;
use Illuminate\Database\Eloquent\Model;

class FormEvaluation extends Model
{
    protected $fillable = [
        'form_type_id',
        'evaluation_date',
        'client_name',
        'company_name',
        'feedback',
        'is_active',
        'email',
        'start_project_date',
];

    public function form()
    {
        return $this->belongsTo(FormType::class,'form_type_id');
    }

    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }

    // evaluation_date
}
