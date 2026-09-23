<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * طرق الدفع — the bank-transfer account(s) shown to students after they accept
 * the agreement. Bank transfer is the only method in this release (no gateway).
 * Linked to cohorts in «الدفعات ← الرسوم والسداد».
 */
class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'طرق الدفع';
    protected static ?string $modelLabel = 'حساب تحويل';
    protected static ?string $pluralModelLabel = 'طرق الدفع';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الحساب')->schema([
                Forms\Components\Select::make('type')->label('طريقة الدفع')->options(PaymentMethod::TYPES)
                    ->default('bank_transfer')->required()->native(false)->selectablePlaceholder(false),
                Forms\Components\TextInput::make('name')->label('اسم داخلي')->required()->maxLength(120)
                    ->helperText('للتمييز داخل لوحة التحكم فقط، مثال: «الحساب الرئيسي».'),
                Forms\Components\TextInput::make('bank_name')->label('اسم البنك')->required()->maxLength(120),
                Forms\Components\TextInput::make('beneficiary_name')->label('اسم المستفيد')->required()->maxLength(190),
                Forms\Components\TextInput::make('iban')->label('رقم الآيبان (IBAN)')->required()
                    ->dehydrateStateUsing(fn (?string $state) => strtoupper(preg_replace('/\s+/', '', (string) $state)))
                    ->rule('regex:/^\s*S\s*A(\s*\d){22}\s*$/i')
                    ->validationMessages(['regex' => 'رقم الآيبان السعودي يبدأ بـ SA ويليه 22 رقمًا.'])
                    ->extraInputAttributes(['dir' => 'ltr']),
                Forms\Components\TextInput::make('account_number')->label('رقم الحساب (اختياري)')->maxLength(40)
                    ->extraInputAttributes(['dir' => 'ltr']),
                Forms\Components\Textarea::make('instructions')->label('تعليمات التحويل للطالب')->rows(4)->maxLength(2000)
                    ->helperText('مثال: اكتب رقم طلبك في خانة ملاحظات التحويل.')->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true)
                    ->helperText('الحساب غير المفعّل لا يظهر لأي طالب.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('الطريقة')->formatStateUsing(fn ($state) => PaymentMethod::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('bank_name')->label('البنك'),
                Tables\Columns\TextColumn::make('iban')->label('IBAN')->fontFamily('mono')->size('sm'),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->since(),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
