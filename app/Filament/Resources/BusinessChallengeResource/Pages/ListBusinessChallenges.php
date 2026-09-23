<?php

namespace App\Filament\Resources\BusinessChallengeResource\Pages;

use App\Enums\ChallengeStatus;
use App\Filament\Resources\BusinessChallengeResource;
use App\Models\BusinessChallenge;
use Filament\Actions\Action;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBusinessChallenges extends ListRecords
{
    protected static string $resource = BusinessChallengeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('يصدّر التحديات الظاهرة حسب البحث والفلاتر الحالية')
                ->action(fn () => BusinessChallengeResource::exportCsv($this->getFilteredSortedTableQuery()->get())),
        ];
    }

    /** Quick status tabs above the table. */
    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('الكل')];
        foreach (ChallengeStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status->value))
                ->badge(fn () => BusinessChallenge::where('status', $status->value)->count() ?: null)
                ->badgeColor($status->getColor());
        }

        return $tabs;
    }
}
