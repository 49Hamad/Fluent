<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\StudentApplicationResource;
use App\Models\Cohort;
use App\Models\StudentApplication;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Key numbers for the cohort that is currently receiving applications. */
class StudentApplicationsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -10;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view_any_student::application');
    }

    protected function getStats(): array
    {
        $cohort = Cohort::currentForApplications();
        $q = fn () => StudentApplication::query()->when($cohort, fn ($x) => $x->where('cohort_id', $cohort->id));
        $count = fn (array $statuses) => $q()->whereIn('status', array_map(fn ($s) => $s->value, $statuses))->count();
        $url = StudentApplicationResource::getUrl('index');

        return [
            Stat::make('إجمالي الطلبات', $q()->count())->icon('heroicon-o-inbox-stack')->url($url)
                ->description($cohort
                    ? $cohort->name . ' — ' . $cohort->registration_status->getLabel()
                    : 'لا توجد دفعة مفتوحة للتسجيل الآن'),
            Stat::make('جديدة (تم الاستلام)', $count([ApplicationStatus::Received]))->color('warning')->icon('heroicon-o-sparkles')
                ->url($url . '?activeTab=received'),
            Stat::make('قيد المراجعة / المقابلة', $count([ApplicationStatus::Review, ApplicationStatus::Interview]))->icon('heroicon-o-magnifying-glass'),
            Stat::make('مقبول (مبدئيًا + نهائيًا)', $count([ApplicationStatus::PreliminaryAccepted, ApplicationStatus::FinalAccepted]))
                ->color('success')->icon('heroicon-o-check-badge'),
        ];
    }
}
