<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Registration state of a cohort, controlled from Filament → الدفعات.
 * Maps 1:1 to the states the approved application page already supports.
 */
enum RegistrationStatus: string implements HasLabel, HasColor
{
    case Open = 'open';
    case Waitlist = 'waitlist';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open     => 'التسجيل مفتوح',
            self::Waitlist => 'قائمة انتظار',
            self::Closed   => 'التسجيل مغلق',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open     => 'success',
            self::Waitlist => 'warning',
            self::Closed   => 'gray',
        };
    }

    /** Does this state accept new applications? */
    public function acceptsApplications(): bool
    {
        return $this !== self::Closed;
    }
}
