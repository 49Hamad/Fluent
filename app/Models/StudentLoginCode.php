<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLoginCode extends Model
{
    public const UPDATED_AT = null;

    public const TTL_MINUTES = 10;
    public const MAX_ATTEMPTS = 5;

    protected $fillable = ['student_id', 'code_hash', 'expires_at', 'attempts', 'consumed_at'];

    protected $hidden = ['code_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /** Keyed hash (HMAC-SHA256 with the app key). The plain code is never stored. */
    public static function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
