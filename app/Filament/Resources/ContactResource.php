<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Contact;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Mail\ReplayMessaegMail;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Mail;

use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\RichEditor;
use App\Filament\Resources\ContactResource\Pages;


class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'قسم تواصل معنا ';
    protected static ?string $navigationModel = 'قسم تواصل معنا ';
    protected static ?string $modelLabel = 'قسم تواصل معنا ';
    protected static ?int $navigationSort = 11;

    public static function getPluralLabel(): string
    {
        return __('قسم تواصل معنا ');
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
        ->label('الاسم')
        ->required()
        ->maxLength(255),
    Forms\Components\TextInput::make('email')
        ->label('البريد الإلكتروني')
        ->email()
        ->required()
        ->maxLength(255),
    Forms\Components\TextInput::make('subject')
        ->label('الموضوع')
        ->required()
        ->maxLength(255),
    Forms\Components\TextInput::make('extra_services')
        ->label('الخدمة المختارة')
        ->required()
        ->maxLength(255),
    Forms\Components\Textarea::make('description')
        ->label('الوصف')
        ->required()
        ->columnSpanFull(),
])->columns(2)



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('الاسم')
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->label('البريد الإلكتروني')
                ->searchable(),
            Tables\Columns\TextColumn::make('subject')
                ->label('الموضوع')
                ->searchable(),
            Tables\Columns\TextColumn::make('extra_services')
                ->label('خدمات إضافية')
                ->searchable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('تاريخ التحديث')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make(
                    [
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\DeleteAction::make(),
                        Tables\Actions\Action::make('send email')
                        ->label('الرد')
                            ->action(function (array $data,Contact $record): void {

                                $IS_SEND = false;
                                    if($record['email'])
                                    {
                                        $IS_SEND = true;
                                    Mail::to($record['email'])->send(new ReplayMessaegMail($data['title'], $data['body'],$data['image']));


                                    }
                                    else{
                                       Notification::make()
                                    ->danger()
                                    ->seconds(5)
                                     ->persistent()
                                    ->title("لم يتم الأرسال")
                                    ->body("لا يوجد بريد إلكتروني للمستخدم   "  . "    ---    ".  $record['name']  )
                                     ->send();
                                    }


                                    if($IS_SEND == true)
                                    {
                                        Notification::make()
                                        ->title('تم ارسال الرسالة')
                                        ->success()
                                        ->body('تم ارسال الرسالة الى '. $record['name'])
                                        ->send();
                                        $record->update(['is_read' => $IS_SEND]);

                                }
                                $IS_SEND == false;

                            })
                            ->form([
                                Forms\Components\TextInput::make('title')

                                   ->label(__('العنوان'))
                                    ->required(),

                                Forms\Components\FileUpload::make('image')
                                    ->label(__('رفع ملف ( اختياري )'))
                                    // ->image()
                                    // acpect only image
                                    // ->acceptedFileTypes(['application/pdf'])
                                    ->imageEditor(),

                                    RichEditor::make('body')
                                ->required()->label(__('محتوى الرساله'))
                                ->toolbarButtons([

                                    'blockquote',
                                    'bold',
                                    'bulletList',
                                    'codeBlock',
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

                                    ->columnSpan('full'),

                            ])->keyBindings(['command+p', 'ctrl+p'])
                    ]
                )

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
            'index' => Pages\ListContacts::route('/'),
            // 'create' => Pages\CreateContact::route('/create'),
            // 'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
