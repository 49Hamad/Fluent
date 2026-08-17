<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Hero;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\HeroResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\HeroResource\RelationManagers;

class HeroResource extends Resource
{
    protected static ?string $model = Hero::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'البانر';
    protected static ?string $navigationModel = 'البانر';
    protected static ?string $modelLabel = 'البانر';
    protected static ?int $navigationSort = 1;

    public static function getPluralLabel(): string
    {
        return __('البانر');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Section::make()->schema([
                Forms\Components\TextInput::make('description')
                ->label('الوصف')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(100),
                Forms\Components\TextInput::make('link_3d')
                ->label('رابط 3D')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('button_text')
                ->label('نص الزر الاستشارة')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('button_link')
                ->label('رابط الاستشارة')
                    ->required()
                    ->maxLength(255),


                                        Forms\Components\ToggleButtons::make('is_button_video')
                                        ->label(__('هل تريد إظهار الفيديو ؟'))
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


                Forms\Components\TextInput::make('button_video_link')
                ->label('رابط الفيديو')
                    ->required()
                    ->columnSpanFull()
                    ->hidden(fn (Get $get): bool => !$get('is_button_video'))
                    ->maxLength(255),
               ])->columns(3)

                    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('button_text')
                ->label('نص الزر الاستشارة'),
                Tables\Columns\TextColumn::make('button_link')
                ->label('رابط الاستشارة'),
                Tables\Columns\TextColumn::make('button_video_link')
                ->label('رابط الفيديو'),
                Tables\Columns\IconColumn::make('is_button_video')
                ->label(__(' إظهار الفيديو ؟'))
                    ->boolean(),
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
                    ->label('إضافة بانر')
                    ->url(route('filament.admin.resources.heroes.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);
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
            'index' => Pages\ListHeroes::route('/'),
            'create' => Pages\CreateHero::route('/create'),
            'edit' => Pages\EditHero::route('/{record}/edit'),
        ];
    }
}
