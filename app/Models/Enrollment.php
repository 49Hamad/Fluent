<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Seat-confirmation track of one application (agreement → payment → seat).
 * Separate from the application status.
 */
class Enrollment extends Model
{
    protected $fillable = [
        'student_application_id', 'payment_status', 'payment_status_changed_at',
        'payment_method_type', 'payment_method_id', 'amount', 'currency',
        'reupload_reason', 'payment_verified_at', 'payment_verified_by',
        'seat_confirmed_at', 'seat_confirmed_by', 'internal_notes',
    ];

    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'payment_status_changed_at' => 'datetime',
        'amount' => 'decimal:2',
        'payment_verified_at' => 'datetime',
        'seat_confirmed_at' => 'datetime',
    ];

    protected $hidden = ['internal_notes', 'payment_verified_by', 'seat_confirmed_by'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(StudentApplication::class, 'student_application_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class)->latest('id');
    }

    public function latestReceipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class)->latestOfMany();
    }

    public function events(): HasMany
    {
        return $this->hasMany(EnrollmentEvent::class)->latest('id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function seatConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seat_confirmed_by');
    }
}
