<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Company challenge status (managed by the Fluent team in Filament). */
enum ChallengeStatus: string implements HasLabel, HasColor
{
    case New         = 'new';
    case Studying    = 'studying';
    case Contacted   = 'contacted';
    case Approved    = 'approved';
    case NotSuitable = 'not_suitable';
    case Closed      = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::New         => 'جديد',
            self::Studying    => 'قيد الدراسة',
            self::Contacted   => 'تم التواصل',
            self::Approved    => 'معتمد للمحاكاة',
            self::NotSuitable => 'غير مناسب',
            self::Closed      => 'مغلق',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New         => 'warning',
            self::Studying    => 'info',
            self::Contacted   => 'primary',
            self::Approved    => 'success',
            self::NotSuitable => 'danger',
            self::Closed      => 'gray',
        };
    }
}
