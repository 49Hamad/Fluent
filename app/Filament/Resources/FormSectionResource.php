<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormSectionResource\Pages;
use App\Filament\Resources\FormSectionResource\RelationManagers;
use App\Models\FormSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FormSectionResource extends Resource
{
    protected static ?string $model = FormSection::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'أقسام التقييم';
    protected static ?string $navigationModel = 'أقسام التقييم';
    protected static ?string $modelLabel = 'أقسام التقييم';
    protected static ?int $navigationSort = 1;

    public static function getPluralLabel(): string
    {
        return __('أقسام التقييم');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('اسم القسم')
                    ->columnSpanFull()
                    ->maxLength(50),
                    Forms\Components\ToggleButtons::make('is_active')
                    ->label(__('هل تريد إظهار القسم ؟'))
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('اسم القسم')

                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                ->label(__('هل تريد إظهار القسم ؟')),
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
            'index' => Pages\ListFormSections::route('/'),
            // 'create' => Pages\CreateFormSection::route('/create'),
            // 'edit' => Pages\EditFormSection::route('/{record}/edit'),
        ];
    }
}
