<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\AddingScript;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use App\Filament\Resources\AddingScriptResource\Pages;

class AddingScriptResource extends Resource
{
    protected static ?string $model = AddingScript::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

  
    public static function getPluralLabel(): string
    {
        return __('Scripts');
    }
    public static function getModelLabel(): string
    {
        return __('Scripts');
    }
    public static function getNavigationLabel(): string
    {
        return   __('Scripts');

    }
    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Section::make()
              ->schema(
                [
                    Forms\Components\TextInput::make('name')
                    ->label(__('الاسم'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('script')
                    ->label('نص البرنامج النصي (أضف defer في وسم الفتح)')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('status')
                    ->label(__('نشط'))
                    ->required(),

                ]
              ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label(__('الاسم'))
                ->searchable(),
            Tables\Columns\ToggleColumn::make('status')
                ->label(__('نشط'))
                ->sortable(),
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
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListAddingScripts::route('/'),
            // 'create' => Pages\CreateAddingScript::route('/create'),
            // 'view' => Pages\ViewAddingScript::route('/{record}'),
            // 'edit' => Pages\EditAddingScript::route('/{record}/edit'),
        ];
    }
}
