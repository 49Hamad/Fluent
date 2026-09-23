<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class StudentApplication extends Model
{
    public const CV_DISK = 'applications';

    protected $fillable = [
        'reference', 'student_id', 'cohort_id', 'status', 'status_changed_at', 'submitted_via_waitlist',
        'full_name', 'email', 'phone', 'gender', 'city', 'university', 'major', 'study_status', 'graduation_year',
        'motivation', 'gaps', 'gaps_other', 'has_experience', 'experience_details', 'team_scenario', 'weekly_commitment',
        'linkedin_url', 'portfolio_url', 'cv_path', 'cv_original_name', 'cv_size', 'consented_at',
        'internal_notes', 'interview_at', 'interview_mode', 'interview_location', 'interview_note',
    ];

    protected $casts = [
        'status' => ApplicationStatus::class,
        'status_changed_at' => 'datetime',
        'submitted_via_waitlist' => 'boolean',
        'gaps' => 'array',
        'has_experience' => 'boolean',
        'graduation_year' => 'integer',
        'cv_size' => 'integer',
        'consented_at' => 'datetime',
        'interview_at' => 'datetime',
    ];

    /** Never exposed if this model is ever serialised for a student-facing screen. */
    protected $hidden = ['internal_notes', 'cv_path'];

    public const INTERVIEW_MODES = [
        'in_person' => 'حضوري',
        'online'    => 'عن بُعد',
    ];

    protected static function booted(): void
    {
        // Remove the private CV file when an application is deleted.
        static::deleted(function (self $application) {
            if ($application->cv_path) {
                Storage::disk(self::CV_DISK)->delete($application->cv_path);
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(StudentApplicationStatusChange::class)->latest('id');
    }

    /* ---- Seat-confirmation workflow (agreement → payment → seat) ---- */

    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    public function agreementAcceptance(): HasOne
    {
        return $this->hasOne(AgreementAcceptance::class);
    }

    public function mediaConsents(): HasMany
    {
        return $this->hasMany(MediaConsent::class)->latest('id');
    }

    /** Current media consent = the latest decision (null = not decided yet). */
    public function latestMediaConsent(): HasOne
    {
        return $this->hasOne(MediaConsent::class)->latestOfMany();
    }

    /** Agreement / payment evidence exists → the application must not be deleted. */
    public function hasEnrollmentEvidence(): bool
    {
        return $this->agreementAcceptance()->exists()
            || $this->mediaConsents()->exists()
            || PaymentReceipt::whereHas('enrollment', fn ($q) => $q->where('student_application_id', $this->id))->exists();
    }
}
