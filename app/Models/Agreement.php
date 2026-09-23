<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Agreement extends Model
{
    protected $fillable = ['cohort_id', 'title', 'version', 'content', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(AgreementAcceptance::class);
    }

    /** A version someone has accepted can no longer be edited — create a new version instead. */
    public function isLocked(): bool
    {
        return $this->exists && $this->acceptances()->exists();
    }

    /** The active agreement for a cohort: its own, otherwise the active general one. */
    public static function activeFor(?Cohort $cohort): ?self
    {
        if ($cohort) {
            $own = static::where('is_active', true)->where('cohort_id', $cohort->id)->latest('id')->first();
            if ($own) {
                return $own;
            }
        }

        return static::where('is_active', true)->whereNull('cohort_id')->latest('id')->first();
    }

    /** Safe HTML for display (Markdown; raw HTML in the text is escaped). */
    public static function renderContent(string $markdown): string
    {
        return Str::markdown($markdown, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
    }
}
