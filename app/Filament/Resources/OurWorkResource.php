<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\OurWork;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OurWorkResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OurWorkResource\RelationManagers;

class OurWorkResource extends Resource
{
    protected static ?string $model = OurWork::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    protected static ?string $navigationLabel = 'اعمالنا ';
    protected static ?string $navigationModel = 'اعمالنا ';
    protected static ?string $modelLabel = 'اعمالنا ';
    protected static ?int $navigationSort = 8;

    public static function getPluralLabel(): string
    {
        return __('اعمالنا ');
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
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
            Forms\Components\TextInput::make('link')
            ->label('الرابط')
            ->url()
                ->maxLength(255),
            Forms\Components\FileUpload::make('image')
            ->label('الصورة')
                ->image()
                ->maxSize('2048')
                ->columnSpanFull()
                ->required(),
              ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('link')
                ->label('الرابط')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
            ->label('الصورة')
            ,
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
            'index' => Pages\ListOurWorks::route('/'),
            'create' => Pages\CreateOurWork::route('/create'),
            'edit' => Pages\EditOurWork::route('/{record}/edit'),
        ];
    }
}
