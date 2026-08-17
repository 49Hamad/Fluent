<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ContactText;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ContactTextResource\Pages;
use App\Filament\Resources\ContactTextResource\RelationManagers;

class ContactTextResource extends Resource
{
    protected static ?string $model = ContactText::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-ripple';
    protected static ?string $navigationLabel = 'عنوان قسم تواصل معنا ';
    protected static ?string $navigationModel = 'عنوان قسم تواصل معنا ';
    protected static ?string $modelLabel = 'عنوان قسم تواصل معنا ';
    protected static ?int $navigationSort = 10;

    public static function getPluralLabel(): string
    {
        return __('عنوان قسم تواصل معنا ');
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
            Forms\Components\TextInput::make('description')
            ->label('الوصف')
                ->maxLength(50),
             ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->label('العنوان'),
                Tables\Columns\TextColumn::make('description')
                ->label('الوصف'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Action::make('create')
                    ->label('إضافة إنجازاتنا')
                    ->url(route('filament.admin.resources.contact-texts.create'))
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
            'index' => Pages\ListContactTexts::route('/'),
            'create' => Pages\CreateContactText::route('/create'),
            // 'edit' => Pages\EditContactText::route('/{record}/edit'),
        ];
    }
}
