<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use App\Filament\Resources\HeroResource;
use App\Filament\Resources\UserResource;
use Filament\Navigation\NavigationGroup;
use App\Filament\Resources\AboutResource;
use App\Filament\Resources\ClientResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\OurWorkResource;
use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\SettingResource;
use App\Filament\Resources\FormTypeResource;
use App\Filament\Resources\ConsultingResource;
use App\Filament\Resources\NumberTalkResource;
use App\Filament\Resources\OurPartnerResource;
use App\Filament\Resources\AchievementResource;
use App\Filament\Resources\ContactTextResource;
use App\Filament\Resources\FormSectionResource;
use App\Filament\Resources\OurWorkTextResource;
use App\Filament\Resources\ServiceTextResource;
use App\Filament\Resources\WhyChooseUsResource;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Resources\AddingScriptResource;
use App\Filament\Resources\ExtraServiceResource;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Resources\FormEvaluationResource;
use App\Filament\Resources\GoogleAnalyticResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use BezhanSalleh\FilamentGoogleAnalytics\Pages\FilamentGoogleAnalyticsDashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->brandLogo(asset('front/assets/images/logo-dark.jpg'))
            ->brandLogoHeight('1.5rem')
            ->id('admin')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make('الموظفين')
                        ->items([
                            ...UserResource::getNavigationItems(),
                            ...RoleResource::getNavigationItems(),
                        ]),

                        NavigationGroup::make('إدارة  التقييمات')
                        ->items([
                            ...FormSectionResource::getNavigationItems(),
                            ...FormTypeResource::getNavigationItems(),
                            ...FormEvaluationResource::getNavigationItems(),
                        ]),

                    NavigationGroup::make('إدارة المحتوى')
                        ->items([
                            ...HeroResource::getNavigationItems(),
                            ...AboutResource::getNavigationItems(),
                            ...WhyChooseUsResource::getNavigationItems(),
                            ...NumberTalkResource::getNavigationItems(),
                            ...ServiceTextResource::getNavigationItems(),
                            ...ServiceResource::getNavigationItems(),
                            ...OurWorkTextResource::getNavigationItems(),
                            ...OurWorkResource::getNavigationItems(),
                            ...ConsultingResource::getNavigationItems(),
                            ...AchievementResource::getNavigationItems(),
                            ...ClientResource::getNavigationItems(),
                            ...OurPartnerResource::getNavigationItems(),
                            ...ContactTextResource::getNavigationItems(),
                            ...ExtraServiceResource::getNavigationItems(),
                            ...ContactResource::getNavigationItems(),

                        ]),


                        NavigationGroup::make('إعدادات النظام')
                        ->items([
                            ...FilamentGoogleAnalyticsDashboard::getNavigationItems(),
                            ...SettingResource::getNavigationItems(),
                            ...AddingScriptResource::getNavigationItems(),
                        ]),


                ]);
            })
            ->path('cpanel-admin-system.pages')
            ->login()
            ->darkMode(false)
            ->userMenuItems([
                MenuItem::make()
                    ->label('الرجوع الى الموقع')
                    ->url(fn (): string =>  '../' )
                    ->color('danger')
                    ->icon('heroicon-o-arrow-uturn-left'),

            ])
            ->colors([
                'primary' => Color::Amber,
                ])
                ->plugins([
                    \BezhanSalleh\FilamentGoogleAnalytics\FilamentGoogleAnalyticsPlugin::make(),
                    FilamentApexChartsPlugin::make(),
                    FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa()
            ->databaseNotifications()
            ;
    }
}
