<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A person who applied to Fluent (identified by e-mail).
 * Separate from employees (App\Models\User) on purpose: students sign in
 * through their own "student" guard with a one-time e-mail code (no
 * password) and can never reach Filament.
 * One student → many applications (one per cohort).
 */
class Student extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $fillable = ['email', 'full_name', 'phone'];

    /** Students have no password. */
    public function getAuthPassword(): string
    {
        return '';
    }

    /** No "remember me" for students. */
    public function getRememberTokenName(): string
    {
        return '';
    }

    public function applications(): HasMany
    {
        return $this->hasMany(StudentApplication::class)->latest();
    }

    public function loginCodes(): HasMany
    {
        return $this->hasMany(StudentLoginCode::class);
    }
}
