<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\FormType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Crypt;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ViewEntry;
use App\Filament\Resources\FormTypeResource\Pages;
use Webbingbrasil\FilamentCopyActions\Tables\Actions\CopyAction;

class FormTypeResource extends Resource
{
    protected static ?string $model = FormType::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'النماذج';
    protected static ?string $navigationModel = 'النماذج';
    protected static ?string $modelLabel = 'النماذج';
    protected static ?int $navigationSort = 2;

    public static function getPluralLabel(): string
    {
        return __('النماذج');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([
                    Wizard\Step::make('النموذج')
                        ->description('هنا يتم انشاء بيانات النموذج الاساسية')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Forms\Components\Select::make('form_section_id')
                                        ->required()
                                        ->label('القسم')
                                        ->relationship('formSection', 'name'),
                                    Forms\Components\TextInput::make('title')
                                        ->label('عنوان النموذج')
                                        ->required()
                                        ->maxLength(50),
                                    Forms\Components\Textarea::make('description')
                                        ->label('وصف النموذج')
                                        ->columnSpanFull()
                                        ->maxLength(100),
                                ])->columns(2)
                        ]),

                    Wizard\Step::make('فقرات الأسئلة')
                        ->schema([
                            Repeater::make('questions')
                                ->label('الأسئلة')
                                ->collapsible()
                                ->schema([
                                    Toggle::make('is_required')
                                        ->label('مطلوب؟')
                                        ->columnSpanFull()
                                        ->default(false),

                                    Select::make('question_type')
                                        ->label('نوع السؤال')
                                        ->options([
                                            'اجابة قصيرة' => 'اجابة قصيرة',
                                            'فقرة' => 'فقرة',
                                            'اختيار واحد' => 'اختيار واحد',
                                            'متعدد الأختيارات' => 'متعدد الأختيارات',
                                            'صورة' => 'صورة',
                                            'التقييم' => 'التقييم',
                                            'القائمة المنسدلة' => 'القائمة المنسدلة',
                                        ])
                                        ->reactive()
                                        ->required(),

                                    TextInput::make('question_text')
                                        ->label('نص السؤال')
                                        ->placeholder('السؤال')
                                        ->required()
                                        ->visible(fn($get) => $get('question_type')),

                                    Repeater::make('options')
                                        ->label('الخيارات')
                                        ->schema([
                                            TextInput::make('option_text')
                                                ->label('نص الخيار')
                                                ->required()
                                                ->placeholder(fn($get, $set, $state) => 'الخيار ' . ($get('options') ? count($get('options')) + 1 : 1)),
                                        ])
                                        ->columnSpanFull()
                                        ->columns(1)
                                        ->grid(2)
                                        ->visible(fn($get) => in_array($get('question_type'), ['اختيار واحد', 'متعدد الأختيارات', 'القائمة المنسدلة']))
                                        ->createItemButtonLabel('إضافة خيار'),

                                    Select::make('rating_value')
                                        ->label('قيمة التقييم')
                                        ->required()
                                        ->options(range(1, 10))
                                        ->visible(fn($get) => $get('question_type') === 'التقييم'),

                                    Select::make('rating_type')
                                        ->label('نوع التقييم')
                                        ->required()
                                        ->options([
                                            'نجوم' => 'نجوم',
                                            'قلوب' => 'قلوب',
                                            'إعجاب' => 'إعجاب',
                                        ])
                                        ->visible(fn($get) => $get('question_type') === 'التقييم'),
                                ])
                                ->defaultItems(1)
                                ->columns(2)
                                ->grid(2)
                                ->reorderableWithButtons()
                                ->createItemButtonLabel('إضافة سؤال'),
                        ]),
                ])->columnSpanFull()








            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('formSection.name')
                    ->label('أقسام التقييم')
                    ->numeric()
                    ->sortable(),


                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان النموذج')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                CopyAction::make()
                ->label('نسخ رابط التقييم')
                ->copyable(fn ($record) => url('https://fluent.sa/feedback-form/' . Crypt::encrypt($record->id))),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()

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
            'index' => Pages\ListFormTypes::route('/'),
            'create' => Pages\CreateFormType::route('/create'),
            'edit' => Pages\EditFormType::route('/{record}/edit'),
        ];
    }
}
