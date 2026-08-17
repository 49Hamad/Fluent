<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\NumberTalk;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NumberTalkResource\Pages;
use App\Filament\Resources\NumberTalkResource\RelationManagers;

class NumberTalkResource extends Resource
{
    protected static ?string $model = NumberTalk::class;


    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'الأرقام  تتحدث عن نجاحنا';
    protected static ?string $navigationModel = 'الأرقام  تتحدث عن نجاحنا';
    protected static ?string $modelLabel = 'الأرقام  تتحدث عن نجاحنا';
    protected static ?int $navigationSort = 4;

    public static function getPluralLabel(): string
    {
        return __('الأرقام  تتحدث عن نجاحنا');
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
                ->required()
                ->maxLength(100),


            Repeater::make('counters')
            ->columnSpanFull()
            ->label('الميزات')

            ->schema([
                Forms\Components\TextInput::make('title')
                ->label('العنوان')
                ->required()
                ->maxLength(50),

                Forms\Components\TextInput::make('numbers')
                ->label('الاحصائية')
                ->required()
                ->numeric()
                ->minValue(1)
                ->maxValue(100000),

                Forms\Components\FileUpload::make('icon')
                ->label('الأيقونة')
                ->columnSpanFull()
                ->required(),

            ])
            ->columns(2)
            ->grid(2)
            ->defaultItems(1)
            ->maxItems(2),

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
            ->emptyStateActions([
                Action::make('create')
                    ->label('إضافة بيانات')
                    ->url(route('filament.admin.resources.number-talks.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListNumberTalks::route('/'),
            'create' => Pages\CreateNumberTalk::route('/create'),
            'edit' => Pages\EditNumberTalk::route('/{record}/edit'),
        ];
    }
}
