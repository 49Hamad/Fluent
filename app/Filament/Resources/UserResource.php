<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;


    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'إدارة الموظفين';
    protected static ?string $navigationLabel = 'الموظفين';
    protected static ?string $navigationModel = 'الموظفين';
    protected static ?string $modelLabel = 'الموظفين';
    protected static ?int $navigationSort = 1;

    public static function getPluralLabel(): string
    {
        return __('الموظفين');
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                    ->label('اسم الموظف')
                    ->required()
                    ->string()
                    ->minLength(3)
                    ->maxLength(50),
                    Forms\Components\TextInput::make('position')
                    ->label('المسمى الوظيفي')
                    ->string()
                    ->minLength(3)
                    ->maxLength(50)
                    ->maxLength(50),
                Forms\Components\TextInput::make('email')
                ->label('البريد الالكتروني')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                    Forms\Components\TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->revealable()
                    ->label('كلمة المرور')
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create') ,


                    Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->label('اختر صلاحية الوصول للمستخدم')
                     ->columnSpanFull()
                     ->preload()
                     ->searchable(),

                     Forms\Components\ToggleButtons::make('is_active')
                     ->label(__('هل تريد الموظف الوصول الى لوحة التحكم ؟'))
                     ->boolean()
                     ->columnSpanFull()
                     ->dehydrated()
                     ->live()
                     ->grouped()
                     ->options([
                         '1' => __('نعم'),
                         '0' => __('لا'),
                     ])
                     ->icons([
                         '1' => 'heroicon-o-check',
                         '0' => 'heroicon-o-x-circle',
                     ])
                     ->default(0),
                ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('اسم الموظف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                ->label('البريد الالكتروني')
                    ->searchable(),

                Tables\Columns\TextColumn::make('position')
                ->label('المسمى الوظيفي')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                ->label(__('هل تريد الموظف الوصول الى لوحة التحكم ؟'))

                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                ->label('تاريخ أنشاء الموظف')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                ->label('تاريخ تحديث بيانات الموظف')
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
                Tables\Actions\DeleteAction::make() ->hidden(fn($record) => \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::id()  === $record->id),


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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'view' => Pages\ViewUser::route('/{record}'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
