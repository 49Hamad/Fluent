<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\About;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AboutResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AboutResource\RelationManagers;

class AboutResource extends Resource
{
    protected static ?string $model = About::class;


    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'من نحن';
    protected static ?string $navigationModel = 'من نحن';
    protected static ?string $modelLabel = 'من نحن';
    protected static ?int $navigationSort = 2;

    public static function getPluralLabel(): string
    {
        return __('من نحن');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                ->label('العنوان')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(20),
                Forms\Components\Textarea::make('description')
                ->label('الوصف')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(1000)
                    ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->label('العنوان')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListAbouts::route('/'),
            // 'create' => Pages\CreateAbout::route('/create'),
            // 'edit' => Pages\EditAbout::route('/{record}/edit'),
        ];
    }
}
