<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A person who applied to Fluent (identified by e-mail).
 * Separate from employees (App\Models\User) on purpose.
 * One student → many applications (one per cohort).
 */
class Student extends Model
{
    protected $fillable = ['email', 'full_name', 'phone'];

    public function applications(): HasMany
    {
        return $this->hasMany(StudentApplication::class)->latest();
    }
}
