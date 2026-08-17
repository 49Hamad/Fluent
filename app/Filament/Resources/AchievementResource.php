<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Achievement;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AchievementResource\Pages;
use App\Filament\Resources\AchievementResource\RelationManagers;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;


    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'أبرز إنجازاتنا';
    protected static ?string $navigationModel = 'أبرز إنجازاتنا';
    protected static ?string $modelLabel = 'أبرز إنجازاتنا';
    protected static ?int $navigationSort = 9;

    public static function getPluralLabel(): string
    {
        return __('أبرز إنجازاتنا');
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
                    ->maxLength(100)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->label('الصورة')
                    ->maxSize('2048')
                    ->columnSpanFull()
                    ->required(),

                Repeater::make('achievements')
                ->columnSpanFull()
                ->label('إنجازاتنا')

                ->schema([
                    Forms\Components\TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(150),
                ])
                ->grid(2)
                ->defaultItems(1)
                ->maxItems(4),
                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->label('العنوان'),

                Tables\Columns\ImageColumn::make('image')
                ->label('الصورة'),

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
                    ->label('إضافة إنجازاتنا')
                    ->url(route('filament.admin.resources.achievements.create'))
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
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
