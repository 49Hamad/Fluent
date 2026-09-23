<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgreementResource\Pages;
use App\Models\Agreement;
use App\Models\Cohort;
use App\Support\AgreementTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * الاتفاقيات — versioned participation agreements.
 * - One active agreement per cohort (or one active general agreement).
 * - A version that any student has accepted is LOCKED: only "active" can be
 *   changed; edits go into a new version («نسخة جديدة»). Each acceptance also
 *   stores a frozen copy, so later edits never alter what a student accepted.
 */
class AgreementResource extends Resource
{
    protected static ?string $model = Agreement::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'الاتفاقيات';
    protected static ?string $modelLabel = 'اتفاقية';
    protected static ?string $pluralModelLabel = 'الاتفاقيات';
    protected static ?int $navigationSort = 5;

    /** Placeholders of the starting draft that must be completed before activation. */
    public const PLACEHOLDER = '[تحدد Fluent';

    public static function form(Form $form): Form
    {
        $locked = fn (?Model $record) => $record instanceof Agreement && $record->isLocked();

        return $form->schema([
            Forms\Components\Placeholder::make('locked_note')->hiddenLabel()
                ->content('وافق طلاب على هذه النسخة، لذلك لا يمكن تعديل نصها. لإجراء تعديل استخدم «نسخة جديدة».')
                ->visible($locked)->columnSpanFull(),

            Forms\Components\Section::make('الاتفاقية')->schema([
                Forms\Components\TextInput::make('title')->label('العنوان')->required()->maxLength(190)
                    ->default(AgreementTemplate::TITLE)->disabled($locked),
                Forms\Components\TextInput::make('version')->label('رقم النسخة')->required()->maxLength(30)
                    ->default('1.0')->placeholder('مثال: 1.0')->disabled($locked),
                Forms\Components\Select::make('cohort_id')->label('الدفعة')
                    ->options(fn () => Cohort::orderByDesc('id')->pluck('name', 'id')->all())
                    ->placeholder('عامة — لكل الدفعات التي ليس لها اتفاقية خاصة')
                    ->native(false)->disabled($locked),
                Forms\Components\Toggle::make('is_active')->label('مفعّلة (تظهر للطلاب المقبولين مبدئيًا)')
                    ->helperText('تفعيلها يوقف تلقائيًا أي اتفاقية مفعّلة أخرى لنفس الدفعة.')
                    ->rule(fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        if ($value && str_contains((string) $get('content'), self::PLACEHOLDER)) {
                            $fail('أكمل البنود المحددة بين [ ] في النص قبل تفعيل الاتفاقية.');
                        }
                    })->inline(false),
            ])->columns(2),

            Forms\Components\Section::make('نص الاتفاقية')
                ->description('يُكتب بصيغة Markdown: «## » لعنوان البند، و«- » للنقاط. أي HTML يُعرض كنص عادي.')
                ->schema([
                    Forms\Components\MarkdownEditor::make('content')->hiddenLabel()->required()->maxLength(60000)
                        ->default(AgreementTemplate::content())
                        ->toolbarButtons(['bold', 'heading', 'bulletList', 'orderedList', 'undo', 'redo'])
                        ->disabled($locked)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('العنوان')->weight('bold')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('version')->label('النسخة')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('cohort.name')->label('الدفعة')->placeholder('عامة'),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّلة')->boolean(),
                Tables\Columns\TextColumn::make('acceptances_count')->label('موافقات')->counts('acceptances')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->since(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('مفعّلة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(fn (Agreement $r) => $r->isLocked() ? 'عرض' : 'تعديل'),
                self::newVersionAction(),
            ]);
    }

    public static function newVersionAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('new_version')
            ->label('نسخة جديدة')
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            ->visible(fn () => auth()->user()?->can('create', Agreement::class))
            ->form(fn (Agreement $record) => [
                Forms\Components\TextInput::make('version')->label('رقم النسخة الجديدة')->required()->maxLength(30)
                    ->default(self::nextVersion($record->version)),
            ])
            ->modalHeading('إنشاء نسخة جديدة من الاتفاقية')
            ->modalDescription('تُنسخ الاتفاقية بنصها الحالي كنسخة غير مفعّلة لتعدّلها ثم تفعّلها. النسخة الحالية وموافقاتها تبقى كما هي.')
            ->action(function (Agreement $record, array $data) {
                $copy = $record->replicate(['is_active']);
                $copy->version = $data['version'];
                $copy->is_active = false;
                $copy->save();

                return redirect(self::getUrl('edit', ['record' => $copy]));
            });
    }

    public static function nextVersion(string $v): string
    {
        return preg_match('/^(\d+)\.(\d+)$/', $v, $m) ? $m[1] . '.' . ($m[2] + 1) : $v . '-2';
    }

    /** Only one active agreement per cohort (or one active general agreement). */
    public static function deactivateOthers(Agreement $agreement): void
    {
        if (! $agreement->is_active) {
            return;
        }
        Agreement::where('id', '!=', $agreement->id)->where('is_active', true)
            ->where(fn ($q) => $agreement->cohort_id ? $q->where('cohort_id', $agreement->cohort_id) : $q->whereNull('cohort_id'))
            ->update(['is_active' => false]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgreements::route('/'),
            'create' => Pages\CreateAgreement::route('/create'),
            'edit' => Pages\EditAgreement::route('/{record}/edit'),
        ];
    }
}
