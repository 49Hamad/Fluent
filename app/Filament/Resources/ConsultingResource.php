<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Consulting;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ConsultingResource\Pages;
use App\Filament\Resources\ConsultingResource\RelationManagers;

class ConsultingResource extends Resource
{
    protected static ?string $model = Consulting::class;


    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    protected static ?string $navigationLabel = 'قسم الأستشارة';
    protected static ?string $navigationModel = 'قسم الأستشارة';
    protected static ?string $modelLabel = 'قسم الأستشارة';
    protected static ?int $navigationSort = 9;

    public static function getPluralLabel(): string
    {
        return __('قسم الأستشارة');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Section::make()->schema([
                Forms\Components\TextInput::make('title')
                ->required()
                ->label('العنوان')
                ->maxLength(30),

            Forms\Components\TextInput::make('button_text')
            ->label('عنوان الزر')
                ->required()
                ->maxLength(20),
            Forms\Components\TextInput::make('button_link')
            ->label('رابط الزر')
                ->required()
                ->url()
                ->maxLength(255),

                Forms\Components\Textarea::make('description')
                ->label('الوصف')
                ->required()
                ->columnSpanFull()
                ->maxLength(150),

                Forms\Components\ToggleButtons::make('is_active')
                ->label(__('هل تريد إظهار زر الاستشارة ؟'))
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

            ])->columns(3)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('button_text')
                ->label('عنوان الزر')
                    ->searchable(),
                Tables\Columns\TextColumn::make('button_link')
                ->label('رابط الزر')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                ->label(__('هل تريد إظهار زر الاستشارة ؟')),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Action::make('create')
                    ->label('إضافة بانر')
                    ->url(route('filament.admin.resources.consultings.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
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
            'index' => Pages\ListConsultings::route('/'),
            'create' => Pages\CreateConsulting::route('/create'),
            'edit' => Pages\EditConsulting::route('/{record}/edit'),
        ];
    }
}
