<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\FormEvaluation;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FormEvaluationResource\Pages;
use App\Filament\Resources\FormEvaluationResource\RelationManagers;
use Filament\Forms\Components\Textarea;

class FormEvaluationResource extends Resource
{
    protected static ?string $model = FormEvaluation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'التقييمات';
    protected static ?string $navigationModel = 'التقييمات';
    protected static ?string $modelLabel = 'التقييمات';
    protected static ?int $navigationSort = 3;

    public static function getPluralLabel(): string
    {
        return __('التقييمات');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()->schema([
                    Forms\Components\TextInput::make('client_name')
                        ->label(__('اسم العميل'))
                        ->required()
                        ->maxLength(50),

                    Forms\Components\TextInput::make('company_name')
                        ->label(__('اسم الشركة'))
                        ->maxLength(30),

                    Forms\Components\TextInput::make('email')
                        ->label(__('البريد الإلكتروني'))
                        ->required()
                        ->email()
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('start_project_date')
                    ->required()
                        ->label(__('تاريخ بدء المشروع')),

                    Forms\Components\Textarea::make('feedback')
                        ->label(__('المراجعة'))
                        ->required()
                        ->columnSpanFull()
                        ->maxLength(200),

                    Forms\Components\ToggleButtons::make('is_active')
                        ->label(__('هل تريد إظهار المراجعة ؟'))
                        ->boolean()
                        ->columnSpanFull()
                        ->dehydrated()
                        ->live()
                        ->grouped()
                        ->columnSpanFull()
                        ->options([
                            '1' => __('نعم'),
                            '0' => __('لا'),
                        ])
                        ->icons([
                            '1' => 'heroicon-o-check',
                            '0' => 'heroicon-o-x-circle',
                        ])
                        ->default(0),

                ])->columns(4),



                Repeater::make('questions')
                ->label('الأسئلة')
                ->collapsible()
                ->visible(fn ($get) => $get('questions') !== null)
                ->schema([
                    Toggle::make('is_required')
                        ->label('مطلوب؟')
                        ->disabled()
                        ->columnSpanFull(),

                    TextInput::make('question_text')
                        ->label('نص السؤال')
                        ->disabled()
                        ->columnSpanFull()
                        ->required()
                        ->readOnly(),
                        TextInput::make('response_text')
                        ->label('الجواب')
                        ->disabled()
                        ->columnSpanFull()
                        ->required()
                        ->visible(fn ($get) => $get('question_type') === 'اجابة قصيرة')
                        ->readOnly(),

                    Textarea::make('response_text')
                        ->label('الجواب')
                        ->disabled()
                        ->columnSpanFull()
                        ->required()
                        ->visible(fn ($get) => $get('question_type') === 'فقرة')
                        ->readOnly(),

                    Repeater::make('options')
                        ->label('الخيارات')
                        ->schema([
                            TextInput::make('option_text')
                                ->label('نص الخيار')
                                ->required()
                                ->disabled()
                                ->readOnly()
                                ->extraAttributes(fn ($get) => $get('extra_attributes'))



                                ])
                                ->visible(fn ($get) => $get('question_type') === 'اختيار واحد' || $get('question_type') === 'متعدد الأختيارات' || $get('question_type') === 'القائمة المنسدلة')


                        ->defaultItems(1)
                        ->columnSpanFull()
                        ->grid(2)
                        ->disableItemCreation()
                        ->disableItemDeletion()
                        ->disableItemMovement(),

                        TextInput::make('rating_value')
                        ->label('قيمة التقييم')
                        ->required()
                        ->columnSpanFull()
                        ->disabled()
                        ->visible(fn ($get) => $get('question_type') === 'التقييم'),




                        FileUpload::make('response_image')
                        ->label('صورة الاستجابة')
                        ->columnSpanFull()
                        ->visible(fn ($get) => $get('question_type') === 'صورة')
                        ->disabled(),



                ])
                ->columnSpanFull()
                ->grid(2)
                ->disableItemCreation()
                ->disableItemDeletion()
                ->columns(2)



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('form.title')
                ->label(__('اسم نموذج التقييم'))
                ->numeric()
                ->sortable(),

            Tables\Columns\TextColumn::make('client_name')
                ->label(__('اسم العميل'))
                ->searchable(),

            Tables\Columns\TextColumn::make('company_name')
                ->label(__('اسم الشركة'))
                ->searchable(),

            Tables\Columns\TextColumn::make('email')
                ->label(__('البريد الإلكتروني'))
                ->searchable(),

            Tables\Columns\TextColumn::make('start_project_date')
                ->label(__('تاريخ بدء المشروع'))
                ->date()
                ->sortable(),

            Tables\Columns\TextColumn::make('evaluation_date')
                ->label(__('تاريخ التقييم'))
                ->date()
                ->sortable(),

            Tables\Columns\ToggleColumn::make('is_active')
                ->label(__('فعّال')),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('تاريخ الإنشاء'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('تاريخ التحديث'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormEvaluations::route('/'),
            'create' => Pages\CreateFormEvaluation::route('/create'),
            'view' => Pages\ViewFormEvaluation::route('/{record}'),
            'edit' => Pages\EditFormEvaluation::route('/{record}/edit'),
        ];
    }
}
