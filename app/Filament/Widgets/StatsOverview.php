<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Client;
use App\Models\Contact;
use App\Models\OurWork;
use App\Models\Service;
use App\Models\FormType;
use App\Models\FormSection;
use Spatie\Permission\Models\Role;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        return [
            Stat::make('الموظفين', User::count())
            ->chart([4, 2, 10, 3, 6, 4, 2])

            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('warning'),

            Stat::make('الصلاحيات', Role::count())
            ->chart([4, 2, 10, 3,63,45,62,60,54,63])
            ->color('info'),
        Stat::make('الخدمات', Service::count())
        ->chart([9, 2, 8, 3, 5, 4, 3])

            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger'),
        Stat::make('اعمالنا ', OurWork::count())
        ->chart([7, 2, 10, 3, 15, 4, 17])

            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('info'),
        Stat::make('شركاءنا ', Client::count())

            ->chart([7, 2, 10, 3, 15, 4, 2])
            ->color('success'),


        Stat::make('نموذج التواصل معنا  "الإيميلات"', Contact::count())->chart([7, 2, 56,4,85,32]) ->color('warning'),
        Stat::make('أقسام التقييم ', FormSection::count())->chart([7, 2, 10,45,65,4,8,64,4,20,15,62]) ->color('success'),
        Stat::make('نماذج التقييم ', FormType::count())->chart([7, 2, 10, 3, 15, 4, 2]) ->color('info'),


        ];
    }
}
