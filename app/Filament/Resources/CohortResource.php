<?php

namespace App\Filament\Resources;

use App\Enums\RegistrationStatus;
use App\Filament\Resources\CohortResource\Pages;
use App\Models\Cohort;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * الدفعات — create cohorts and open / waitlist / close registration.
 * Only ONE cohort can accept applications at a time; the public
 * application page always uses that cohort.
 */
class CohortResource extends Resource
{
    protected static ?string $model = Cohort::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'الدفعات';
    protected static ?string $modelLabel = 'دفعة';
    protected static ?string $pluralModelLabel = 'الدفعات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الدفعة')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الدفعة')->required()->maxLength(150)
                    ->placeholder('مثال: الدفعة الثانية — خريف 2026'),
                Forms\Components\DatePicker::make('start_date')->label('تاريخ البداية')->native(false),
                Forms\Components\TextInput::make('schedule')->label('الوقت')->maxLength(100)->placeholder('مثال: 9:00 ص – 3:00 م'),
                Forms\Components\TextInput::make('location')->label('الموقع')->maxLength(200),
            ])->columns(2),

            Forms\Components\Section::make('حالة التسجيل')
                ->description('الدفعة التي حالتها «مفتوح» أو «قائمة انتظار» هي التي تستقبل الطلبات من صفحة «سجّل في المحاكاة». لا يمكن فتح دفعتين في نفس الوقت.')
                ->schema([
                    Forms\Components\ToggleButtons::make('registration_status')
                        ->label('')
                        ->options(RegistrationStatus::class)
                        ->inline()
                        ->default(RegistrationStatus::Closed->value)
                        ->required()
                        ->rules([
                            fn (?Cohort $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                $status = $value instanceof RegistrationStatus ? $value : RegistrationStatus::tryFrom((string) $value);
                                if (! $status?->acceptsApplications()) {
                                    return;
                                }
                                $other = Cohort::acceptingApplications()
                                    ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                    ->first();
                                if ($other) {
                                    $fail('الدفعة «' . $other->name . '» تستقبل التسجيل حاليًا. أغلق تسجيلها أولًا.');
                                }
                            },
                        ]),
                ]),

            Forms\Components\Section::make('الرسوم والسداد')
                ->description('تظهر للطالب المقبول مبدئيًا ضمن بيانات الاتفاقية، وتفاصيل الحساب تظهر له بعد موافقته على الاتفاقية.')
                ->schema([
                    Forms\Components\TextInput::make('fee_amount')->label('رسوم المشاركة')->numeric()->minValue(0)->maxValue(1000000)
                        ->step('0.01')->suffix('ريال سعودي')
                        ->helperText('المبلغ الذي يُطلب من الطالب تحويله لتأكيد مقعده.'),
                    Forms\Components\DatePicker::make('payment_deadline')->label('آخر موعد للسداد (اختياري)'),
                    Forms\Components\Select::make('payment_method_id')->label('حساب التحويل البنكي')
                        ->relationship('paymentMethod', 'name', fn ($query) => $query->where('is_active', true))
                        ->native(false)
                        ->helperText('يُدار من «المحاكاة ← طرق الدفع».'),
                ])->columns(3)->collapsible(),

            Forms\Components\Section::make('معلومات للمقبولين نهائيًا')
                ->description('تظهر لاحقًا في مساحة الطالب وفي بريد القبول النهائي.')
                ->schema([
                    Forms\Components\Textarea::make('instructions')->label('تعليمات مهمة')->rows(3)->maxLength(2000),
                    Forms\Components\Textarea::make('what_to_bring')->label('ما يحتاج الطالب تجهيزه')->rows(3)->maxLength(2000),
                ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الدفعة')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('start_date')->label('تاريخ البداية')->date('Y-m-d')->placeholder('—'),
                Tables\Columns\TextColumn::make('registration_status')->label('التسجيل')->badge(),
                Tables\Columns\TextColumn::make('fee_amount')->label('الرسوم')->placeholder('—')
                    ->formatStateUsing(fn ($state) => \App\Services\EnrollmentService::money($state))->toggleable(),
                Tables\Columns\TextColumn::make('applications_count')->label('الطلبات')->counts('applications')->badge()->color('gray'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    self::statusAction(RegistrationStatus::Open, 'فتح التسجيل', 'heroicon-o-lock-open'),
                    self::statusAction(RegistrationStatus::Waitlist, 'قائمة انتظار', 'heroicon-o-queue-list'),
                    self::statusAction(RegistrationStatus::Closed, 'إغلاق التسجيل', 'heroicon-o-lock-closed'),
                ])->label('التسجيل')->icon('heroicon-o-adjustments-horizontal')->button()->color('gray'),
                Tables\Actions\EditAction::make(),
            ]);
    }

    /** One-click open / waitlist / close from the cohorts list. */
    private static function statusAction(RegistrationStatus $status, string $label, string $icon): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('registration_' . $status->value)
            ->label($label)
            ->icon($icon)
            ->color($status->getColor())
            ->visible(fn (Cohort $record) => $record->registration_status !== $status && auth()->user()?->can('update', $record))
            ->requiresConfirmation()
            ->modalHeading(fn (Cohort $record) => $label . ' — ' . $record->name)
            ->modalDescription($status === RegistrationStatus::Closed
                ? 'صفحة التسجيل ستعرض رسالة «التسجيل مغلق» فورًا.'
                : 'صفحة «سجّل في المحاكاة» ستستقبل الطلبات لهذه الدفعة فورًا.')
            ->action(function (Cohort $record) use ($status) {
                if ($status->acceptsApplications()) {
                    $other = Cohort::acceptingApplications()->whereKeyNot($record->getKey())->first();
                    if ($other) {
                        Notification::make()->danger()
                            ->title('لا يمكن فتح دفعتين في نفس الوقت')
                            ->body('الدفعة «' . $other->name . '» تستقبل التسجيل حاليًا. أغلق تسجيلها أولًا.')
                            ->send();
                        return;
                    }
                }
                $record->update(['registration_status' => $status]);
                Notification::make()->success()->title('تم: ' . $status->getLabel() . ' — ' . $record->name)->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCohorts::route('/'),
            'create' => Pages\CreateCohort::route('/create'),
            'edit' => Pages\EditCohort::route('/{record}/edit'),
        ];
    }
}
