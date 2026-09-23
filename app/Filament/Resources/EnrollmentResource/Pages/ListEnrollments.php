<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('الكل')];
        foreach (PaymentStatus::cases() as $s) {
            $tabs[$s->value] = Tab::make($s->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_status', $s->value))
                ->badge(fn () => Enrollment::where('payment_status', $s->value)->count() ?: null)
                ->badgeColor($s->getColor());
        }
        $tabs['seat'] = Tab::make('المقاعد المؤكدة')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('seat_confirmed_at'));

        return $tabs;
    }
}
