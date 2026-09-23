<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentReceipt extends Model
{
    public const DISK = 'receipts';

    public const REVIEW = ['pending' => 'بانتظار المراجعة', 'accepted' => 'مقبول', 'rejected' => 'طُلب استبداله'];

    protected $fillable = ['uuid', 'enrollment_id', 'file_path', 'original_name', 'mime_type', 'size', 'review_status', 'reviewed_at', 'reviewed_by'];

    protected $casts = ['size' => 'integer', 'reviewed_at' => 'datetime'];

    protected $hidden = ['file_path', 'reviewed_by'];

    protected static function booted(): void
    {
        static::creating(fn (self $r) => $r->uuid ??= (string) Str::uuid());
        static::deleted(fn (self $r) => Storage::disk(self::DISK)->delete($r->file_path));
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
