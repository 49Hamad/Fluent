<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ServiceText;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ServiceTextResource\Pages;
use App\Filament\Resources\ServiceTextResource\RelationManagers;

class ServiceTextResource extends Resource
{
    protected static ?string $model = ServiceText::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'عنوان قسم خدماتنا';
    protected static ?string $navigationModel = 'عنوان قسم خدماتنا';
    protected static ?string $modelLabel = 'عنوان قسم خدماتنا';
    protected static ?int $navigationSort = 5;

    public static function getPluralLabel(): string
    {
        return __('عنوان قسم خدماتنا');
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
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->maxLength(100),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->label('العنوان'),
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
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Action::make('create')
                    ->label('إضافة بيانات')
                    ->url(route('filament.admin.resources.service-texts.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ;
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
            'index' => Pages\ListServiceTexts::route('/'),
            'create' => Pages\CreateServiceText::route('/create'),
            'edit' => Pages\EditServiceText::route('/{record}/edit'),
        ];
    }
}
