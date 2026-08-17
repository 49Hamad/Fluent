<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Client;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ClientResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ClientResource\RelationManagers;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'شركاءنا';
    protected static ?string $navigationModel = 'شركاءنا';
    protected static ?string $modelLabel = 'شركاءنا';
    protected static ?int $navigationSort = 12;

    public static function getPluralLabel(): string
    {
        return __('شركاءنا');
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
                Forms\Components\TextInput::make('name')
                ->label('اسم  الجهة')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('link')
                ->label('الرابط')
                ->url()
                ->maxLength(255),

                Forms\Components\FileUpload::make('logo')
                ->label('الشعار')
                    ->required()
                ->columnSpanFull()
                ->image()
                    ->maxSize('1024'),

                Forms\Components\ToggleButtons::make('is_active')
                ->label(__('هل تريد إظهار العميل ؟'))
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
              ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('اسم  الجهة')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('logo')
                ->label('الشعار'),
                Tables\Columns\TextColumn::make('link')
                ->label('الرابط')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                ->label(__('هل تريد إظهار العميل ؟')),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
