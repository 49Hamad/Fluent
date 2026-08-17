<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\WhyChooseUs;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\WhyChooseUsResource\Pages;
use App\Filament\Resources\WhyChooseUsResource\RelationManagers;

class WhyChooseUsResource extends Resource
{
    protected static ?string $model = WhyChooseUs::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'ما يميزنا';
    protected static ?string $navigationModel = 'ما يميزنا';
    protected static ?string $modelLabel = 'ما يميزنا';
    protected static ?int $navigationSort = 3;

    public static function getPluralLabel(): string
    {
        return __('ما يميزنا');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Section::make()->schema([
                Forms\Components\TextInput::make('main_title')
                ->label('العنوان الرئيسي')
                ->required()
                ->maxLength(20),
            Forms\Components\TextInput::make('sub_title')
                ->label('العنوان الفرعي')
                ->required()
                ->maxLength(50),
            Forms\Components\TextInput::make('button_text')
                ->label('نص الزر')
                ->required()
                ->maxLength(20),
            Forms\Components\TextInput::make('button_link')
                ->label('رابط الزر')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('الوصف')
                ->columnSpanFull()
                ->required()
                ->maxLength(255),


                    Repeater::make('features')
                    ->columnSpanFull()
                    ->label('الميزات')

                    ->schema([
                        Forms\Components\TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->maxLength(50),
                    ])
                    ->grid(3)
                    ->defaultItems(1)
                    ->maxItems(4),


              ])->columns(4)


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('main_title')
                ->label('العنوان الرئيسي')
                    ,
                Tables\Columns\TextColumn::make('sub_title')
                ->label('العنوان الفرعي') ,
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
                    ->url(route('filament.admin.resources.why-chooseuses.create'))
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
            'index' => Pages\ListWhyChooseUs::route('/'),
            'create' => Pages\CreateWhyChooseUs::route('/create'),
            // 'edit' => Pages\EditWhyChooseUs::route('/{record}/edit'),
        ];
    }
}
