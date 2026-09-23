<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    protected $fillable = [
        'name', 'start_date', 'schedule', 'location',
        'instructions', 'what_to_bring', 'registration_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'registration_status' => RegistrationStatus::class,
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(StudentApplication::class);
    }

    /** Cohorts whose registration is open or on waitlist. */
    public function scopeAcceptingApplications(Builder $query): Builder
    {
        return $query->whereIn('registration_status', [
            RegistrationStatus::Open->value,
            RegistrationStatus::Waitlist->value,
        ]);
    }

    /**
     * The cohort the public application page is currently for.
     * Only one cohort may accept applications at a time (enforced in Filament).
     */
    public static function currentForApplications(): ?self
    {
        return static::acceptingApplications()->latest('id')->first();
    }
}
