<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Service;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ServiceResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ServiceResource\RelationManagers;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'قسم خدماتنا';
    protected static ?string $navigationModel = 'قسم خدماتنا';
    protected static ?string $modelLabel = 'قسم خدماتنا';
    protected static ?int $navigationSort = 6;

    public static function getPluralLabel(): string
    {
        return __('قسم خدماتنا');
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
                ->label('عنوان الخدمة')
                ->maxLength(50),

                Forms\Components\FileUpload::make('image')
                ->label('صورة الخدمة')
                    ->image()
                    ->required(),


                    Forms\Components\RichEditor::make('description')
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->label('الوصف')
                    ->required()
                    ->columnSpanFull(),


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
                Tables\Columns\TextColumn::make('title')
                ->label('عنوان الخدمة')
                ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                ->label('صورة الخدمة'),
                Tables\Columns\ToggleColumn::make('is_active')
                ->label(__('هل تريد إظهار الخدمة ؟')),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
