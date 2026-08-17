<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Setting;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\SettingResource\Pages;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'إعدادات ';
    protected static ?string $navigationLabel = 'إعدادات العامة';
    protected static ?string $navigationModel = 'إعدادات العامة';
    protected static ?string $modelLabel = 'إعدادات العامة';
    protected static ?int $navigationSort = 3;

    public static function getPluralLabel(): string
    {
        return __('إعدادات العامة');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([




                Tabs::make('Tabs')
                ->tabs([
                    Tab::make(__('روابط التواصل الاجتماعي'))
                        ->schema([
                            Repeater::make('Address')->label(__('روابط التواصل الاجتماعي'))
                                ->schema([
                                    Select::make('social_type')
                                        ->label(__('اختر أيقونة التواصل الاجتماعي'))
                                        ->options([
                                            "email" => "Email",
                                            "phone" => "Phone",
                                            "address" => "Address",
                                        ])
                                        ->live()
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $set('name', $get('social_type'));
                                        }),
                                    TextInput::make('name')
                                        ->label(__('أضف'))
                                        ->required(),
                                ])
                                ->defaultItems(1)
                                ->columns(1)
                                ->grid(3),

                            Repeater::make('social_links')->label(__('روابط التواصل الاجتماعي'))
                                ->schema([
                                    Select::make('social_type')
                                        ->label(__('اختر أيقونة التواصل الاجتماعي'))
                                        ->options([
                                            "Facebook" => "Facebook",
                                            "WhatsApp" => "WhatsApp",
                                            "Instagram" => "Instagram",
                                            "Twitter" => "Twitter",
                                            "LinkedIn" => "LinkedIn",
                                            "Snapchat" => "Snapchat",
                                            "YouTube" => "YouTube",
                                            "TikTok" => "TikTok",
                                            "Telegram" => "Telegram",
                                        ])
                                        ->live()
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $set('name', $get('social_type'));
                                        }),
                                    TextInput::make('name')
                                        ->label(__('أضف العنوان'))
                                        ->required(),
                                ])
                                ->columns(1)
                                ->grid(3),

                            Section::make()
                                ->schema([
                                    TextInput::make('location')
                                        ->label(__('الموقع'))
                                ]),
                        ]),

                    Tab::make(__('الشعار'))
                        ->schema([
                            Section::make()
                                ->schema([
                                    FileUpload::make('headerlogo')
                                        ->label(__('شعار رأس الصفحة'))
                                        ->required()
                                        ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                                        ->directory('settingImages/favIcon')
                                        ->visibility('public')
                                        ->disk('public')
                                        ->imageEditor(),
                                    FileUpload::make('footerlogo')
                                        ->label(__('شعار إسفل الصفحة'))
                                        ->required()
                                        ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                                        ->directory('settingImages/favIcon')
                                        ->visibility('public')
                                        ->disk('public')
                                        ->imageEditor(),
                                ])
                                ->columns(2)
                        ]),

                    Tab::make(__('SEO'))
                        ->schema([
                            Section::make(__('seo -- بيانات لمحرك البحث جوجل'))
                                ->schema([
                                    TextInput::make('meta_title')
                                    ->maxLength(50)
                                        ->label(__('عنوان الميتا')),
                                    FileUpload::make('meta_image')
                                        ->label(__('صورة الميتا'))
                                        ->image()
                                        ->imageEditor(),
                                    TagsInput::make('meta_keywords')
                                        ->label(__('كلمات مفتاحية')),
                                    Textarea::make('meta_description')
                                    ->maxLength(200)
                                        ->label(__('وصف الميتا')),
                                ]),
                        ]),
                ])
                ->columnSpanFull()






            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('headerlogo')
                ->label(__('شعار رأس الصفحة'))
                    ->searchable(),
                Tables\Columns\ImageColumn::make('footerlogo')
                ->label(__('شعار إسفل الصفحة'))
                    ->searchable(),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
