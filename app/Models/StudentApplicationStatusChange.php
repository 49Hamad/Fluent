<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentApplicationStatusChange extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['student_application_id', 'from_status', 'to_status', 'changed_by', 'student_notified'];

    protected $casts = [
        'from_status' => ApplicationStatus::class,
        'to_status' => ApplicationStatus::class,
        'student_notified' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(StudentApplication::class, 'student_application_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
