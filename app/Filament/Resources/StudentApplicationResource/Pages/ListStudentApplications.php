<?php

namespace App\Filament\Resources\StudentApplicationResource\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\StudentApplicationResource;
use Filament\Actions\Action;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListStudentApplications extends ListRecords
{
    protected static string $resource = StudentApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('يصدّر الطلبات الظاهرة حسب البحث والفلاتر الحالية')
                ->action(fn () => StudentApplicationResource::exportCsv(
                    $this->getFilteredSortedTableQuery()->with('cohort')->get()
                )),
        ];
    }

    /** Quick status tabs above the table. */
    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('الكل')];
        foreach (ApplicationStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status->value))
                ->badge(fn () => \App\Models\StudentApplication::where('status', $status->value)->count() ?: null)
                ->badgeColor($status->getColor());
        }

        return $tabs;
    }
}
