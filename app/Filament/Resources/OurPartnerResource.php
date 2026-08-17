<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\OurPartner;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OurPartnerResource\Pages;
use App\Filament\Resources\OurPartnerResource\RelationManagers;

class OurPartnerResource extends Resource
{
    protected static ?string $model = OurPartner::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'عنوان قسم شركاءنا ';
    protected static ?string $navigationModel = 'عنوان قسم شركاءنا ';
    protected static ?string $modelLabel = 'عنوان قسم شركاءنا ';
    protected static ?int $navigationSort = 7;

    public static function getPluralLabel(): string
    {
        return __('عنوان قسم شركاءنا ');
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
                    ->url(route('filament.admin.resources.our-partners.create'))
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
            'index' => Pages\ListOurPartners::route('/'),
            'create' => Pages\CreateOurPartner::route('/create'),
            // 'edit' => Pages\EditOurPartner::route('/{record}/edit'),
        ];
    }
}
