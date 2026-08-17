<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ExtraService;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ExtraServiceResource\Pages;
use App\Filament\Resources\ExtraServiceResource\RelationManagers;

class ExtraServiceResource extends Resource
{
    protected static ?string $model = ExtraService::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'قسم الخدمات الإضافية';
    protected static ?string $navigationModel = 'قسم الخدمات الإضافية';
    protected static ?string $modelLabel = 'قسم الخدمات الإضافية';
    protected static ?int $navigationSort = 7;

    public static function getPluralLabel(): string
    {
        return __('قسم الخدمات الإضافية');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Section::make()->schema([
                Forms\Components\TextInput::make('name')
                ->label('عنوان الخدمة')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\ToggleButtons::make('is_active')
                    ->label(__('هل تريد إظهار الخدمة ؟'))
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
            ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('عنوان الخدمة')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                ->label(__('هل تريد إظهار الخدمة ؟'))
                    ->boolean(),
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
            'index' => Pages\ListExtraServices::route('/'),
            // 'create' => Pages\CreateExtraService::route('/create'),
            // 'edit' => Pages\EditExtraService::route('/{record}/edit'),
        ];
    }
}
