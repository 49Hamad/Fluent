<?php

namespace App\Filament\Resources;

use App\Enums\ChallengeStatus;
use App\Filament\Resources\BusinessChallengeResource\Pages;
use App\Models\BusinessChallenge;
use App\Support\BusinessChallengeForm as Q;
use Filament\Forms;
use Filament\Infolists\Components as I;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * تحديات الشركات — review and manage challenges sent from «شاركنا تحديًا».
 * Challenges are created only by the public form (no "create" here).
 * Access is controlled by Filament Shield permissions (…_business::challenge).
 */
class BusinessChallengeResource extends Resource
{
    protected static ?string $model = BusinessChallenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'تحديات الشركات';
    protected static ?string $modelLabel = 'تحدٍّ';
    protected static ?string $pluralModelLabel = 'تحديات الشركات';
    protected static ?string $recordTitleAttribute = 'org_name';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $n = static::getModel()::where('status', ChallengeStatus::New)->count();

        return $n ? (string) $n : null;   // new (not yet studied) challenges
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'تحديات جديدة لم تُدرس';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /* ------------------------------------------------------------------
       List
       ------------------------------------------------------------------ */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('الرقم المرجعي')->searchable()->copyable()
                    ->fontFamily('mono')->size('sm'),
                Tables\Columns\TextColumn::make('org_name')->label('الجهة')->searchable()->weight('bold')
                    ->description(fn (BusinessChallenge $r) => $r->sector),
                Tables\Columns\TextColumn::make('org_type')->label('نوع الجهة')
                    ->formatStateUsing(fn ($state, BusinessChallenge $r) => self::orgTypeText($r))->toggleable(),
                Tables\Columns\TextColumn::make('sector')->label('القطاع')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contact_name')->label('مسؤول التواصل')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('email')->label('البريد')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')->label('الجوال')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\IconColumn::make('has_confidential_info')->label('قيود / سرية')->boolean()
                    ->trueIcon('heroicon-o-lock-closed')->falseIcon('')->trueColor('warning')->toggleable(),
                Tables\Columns\IconColumn::make('file_path')->label('ملف')->boolean()
                    ->getStateUsing(fn (BusinessChallenge $r) => filled($r->file_path))
                    ->trueIcon('heroicon-o-paper-clip')->falseIcon('')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإرسال')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->since()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options(ChallengeStatus::class)->multiple(),
                Tables\Filters\SelectFilter::make('org_type')->label('نوع الجهة')->options(Q::ORG_TYPE)->multiple(),
                Tables\Filters\Filter::make('created_at')
                    ->label('تاريخ الإرسال')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('تاريخ الإرسال من'),
                        Forms\Components\DatePicker::make('until')->label('إلى'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when(self::day($data['from'] ?? null), fn (Builder $query, $d) => $query->whereDate('created_at', '>=', $d))
                        ->when(self::day($data['until'] ?? null), fn (Builder $query, $d) => $query->whereDate('created_at', '<=', $d)))
                    ->indicateUsing(function (array $data): array {
                        $out = [];
                        if ($d = self::day($data['from'] ?? null)) $out[] = 'من ' . $d;
                        if ($d = self::day($data['until'] ?? null)) $out[] = 'إلى ' . $d;
                        return $out;
                    }),
                Tables\Filters\TernaryFilter::make('has_confidential_info')->label('فيه معلومات سرية أو قيود'),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                self::statusTableAction(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_status')
                    ->label('تغيير الحالة للمحدد')
                    ->icon('heroicon-o-arrow-path')
                    ->form(self::statusFormSchema())
                    ->visible(fn () => auth()->user()?->can('update_business::challenge'))
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data) {
                        $n = 0;
                        foreach ($records as $record) {
                            $n += (int) self::setStatus($record, ChallengeStatus::from($data['status']));
                        }
                        Notification::make()->success()->title('تم حفظ الحالة')->body('تم تحديث ' . $n . ' تحدٍّ.')->send();
                    }),
                Tables\Actions\BulkAction::make('export_selected')
                    ->label('تصدير المحدد (Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Collection $records) => self::exportCsv($records)),
            ]);
    }

    /** Date filter value → 'Y-m-d' (the picker may add a time part). */
    private static function day(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /* ------------------------------------------------------------------
       Status
       ------------------------------------------------------------------ */
    public static function statusFormSchema(?BusinessChallenge $record = null): array
    {
        return [
            Forms\Components\Select::make('status')
                ->label('الحالة الجديدة')
                ->options(ChallengeStatus::class)
                ->default($record?->status?->value)
                ->required()
                ->native(false),
        ];
    }

    public static function statusTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('change_status')
            ->label('تغيير الحالة')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->visible(fn (BusinessChallenge $record) => auth()->user()?->can('update', $record))
            ->form(fn (BusinessChallenge $record) => self::statusFormSchema($record))
            ->modalHeading(fn (BusinessChallenge $record) => 'تغيير حالة تحدي ' . $record->org_name)
            ->modalSubmitActionLabel('حفظ الحالة')
            ->action(function (BusinessChallenge $record, array $data) {
                self::setStatus($record, ChallengeStatus::from($data['status']));
                Notification::make()->success()->title('تم حفظ الحالة')->send();
            });
    }

    /** Returns true if the status actually changed. Server-side permission check. */
    public static function setStatus(BusinessChallenge $record, ChallengeStatus $status): bool
    {
        abort_unless(auth()->user()?->can('update', $record), 403);
        if ($record->status === $status) {
            return false;
        }
        $record->update(['status' => $status, 'status_changed_at' => now()]);

        return true;
    }

    /* ------------------------------------------------------------------
       Attachment download (private file, authorised employees only)
       ------------------------------------------------------------------ */
    public static function downloadFile(BusinessChallenge $record): ?StreamedResponse
    {
        abort_unless(auth()->user()?->can('view', $record), 403);
        $disk = Storage::disk(BusinessChallenge::FILE_DISK);
        if (! $record->file_path || ! $disk->exists($record->file_path)) {
            Notification::make()->danger()->title('الملف غير موجود')->send();
            return null;
        }
        $name = 'تحدي - ' . $record->org_name . ' - ' . $record->reference . '.pdf';

        return $disk->download($record->file_path, $name);
    }

    /* ------------------------------------------------------------------
       Export (CSV that opens in Excel with Arabic text) — no internal notes
       ------------------------------------------------------------------ */
    public static function exportCsv(iterable $records): StreamedResponse
    {
        $headers = ['الرقم المرجعي', 'الحالة', 'تاريخ الإرسال', 'آخر تحديث', 'اسم الجهة', 'نوع الجهة', 'القطاع', 'مسؤول التواصل',
            'المسمى الوظيفي', 'البريد', 'الجوال', 'التحدي', 'من يتأثر', 'الأثر الحالي', 'المتوقع من المشاركين',
            'يستطيعون مشاركة مواد', 'معلومات سرية أو قيود', 'تفاصيل السرية / القيود', 'ملف مرفق'];

        return response()->streamDownload(function () use ($records, $headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // UTF-8 BOM for Excel
            fputcsv($out, $headers);
            foreach ($records as $r) {
                fputcsv($out, [
                    $r->reference, $r->status?->getLabel(), $r->created_at?->format('Y-m-d H:i'), $r->updated_at?->format('Y-m-d H:i'),
                    $r->org_name, self::orgTypeText($r), $r->sector, $r->contact_name,
                    $r->job_title, $r->email, $r->phone, $r->challenge_description, $r->affected_parties, $r->current_impact,
                    self::outputsText($r), $r->can_share_materials ? 'نعم' : 'لا', $r->has_confidential_info ? 'نعم' : 'لا',
                    $r->confidential_details, $r->file_original_name ? 'نعم' : 'لا',
                ]);
            }
            fclose($out);
        }, 'fluent-challenges-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function orgTypeText(BusinessChallenge $r): string
    {
        return $r->org_type === 'other' && $r->org_type_other
            ? 'أخرى: ' . $r->org_type_other
            : (string) Q::label(Q::ORG_TYPE, $r->org_type);
    }

    public static function outputsText(BusinessChallenge $r): string
    {
        return collect($r->expected_outputs ?? [])
            ->map(fn ($k) => $k === 'other' && $r->expected_outputs_other ? 'أخرى: ' . $r->expected_outputs_other : Q::label(Q::EXPECTED_OUTPUTS, $k))
            ->implode('، ');
    }

    /* ------------------------------------------------------------------
       Challenge page (readable — no raw data)
       ------------------------------------------------------------------ */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            I\Section::make()->schema([
                I\TextEntry::make('reference')->label('الرقم المرجعي')->copyable()->fontFamily('mono')->weight('bold'),
                I\TextEntry::make('status')->label('الحالة')->badge(),
                I\TextEntry::make('status_changed_at')->label('آخر تغيير للحالة')->since(),
                I\TextEntry::make('created_at')->label('تاريخ الإرسال')->dateTime('Y-m-d H:i'),
                I\TextEntry::make('updated_at')->label('آخر تحديث')->dateTime('Y-m-d H:i'),
            ])->columns(3),

            I\Section::make('عن الجهة')->schema([
                I\TextEntry::make('org_name')->label('اسم الجهة'),
                I\TextEntry::make('org_type')->label('نوع الجهة')->formatStateUsing(fn ($state, BusinessChallenge $r) => self::orgTypeText($r)),
                I\TextEntry::make('sector')->label('القطاع'),
                I\TextEntry::make('contact_name')->label('اسم مسؤول التواصل'),
                I\TextEntry::make('job_title')->label('المسمى الوظيفي')->placeholder('—'),
                I\TextEntry::make('email')->label('البريد الإلكتروني')->copyable(),
                I\TextEntry::make('phone')->label('رقم الجوال')->copyable(),
            ])->columns(3),

            I\Section::make('عن التحدي')->schema([
                I\TextEntry::make('challenge_description')->label('وش التحدي اللي تواجهه الجهة؟')->columnSpanFull()->prose(),
                I\TextEntry::make('affected_parties')->label('مين يتأثر بهذا التحدي؟')->columnSpanFull()->prose(),
                I\TextEntry::make('current_impact')->label('وش الأثر الحالي للمشكلة على الجهة؟')->columnSpanFull()->prose(),
                I\TextEntry::make('expected_outputs')->label('وش يتوقعون من المشاركين في نهاية المحاكاة؟')->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => Q::label(Q::EXPECTED_OUTPUTS, $state)),
                I\TextEntry::make('expected_outputs_other')->label('«أخرى»')
                    ->visible(fn (BusinessChallenge $r) => filled($r->expected_outputs_other)),
                I\TextEntry::make('can_share_materials')->label('يستطيعون مشاركة معلومات أو مواد تساعد المشاركين؟')
                    ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا'),
                I\TextEntry::make('has_confidential_info')->label('توجد معلومات سرية أو قيود؟')
                    ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا')
                    ->color(fn ($state) => $state ? 'warning' : null),
                I\TextEntry::make('confidential_details')->label('المعلومات السرية أو القيود التي يجب مراعاتها')->columnSpanFull()->prose()
                    ->visible(fn (BusinessChallenge $r) => $r->has_confidential_info),
            ])->columns(2),

            I\Section::make('الملف والموافقة')->schema([
                I\TextEntry::make('file_original_name')->label('ملف عن التحدي أو الجهة')->placeholder('لم يُرفق ملف')
                    ->suffix(fn (BusinessChallenge $r) => $r->file_size ? ' · ' . ($r->file_size >= 1048576
                        ? number_format($r->file_size / 1048576, 1) . ' MB'
                        : max(1, round($r->file_size / 1024)) . ' KB') : null)
                    ->helperText(fn (BusinessChallenge $r) => $r->file_path ? 'للتحميل استخدم زر «تحميل الملف» أعلى الصفحة.' : null),
                I\TextEntry::make('consented_at')->label('وافق على استخدام المعلومات')->dateTime('Y-m-d H:i'),
            ])->columns(2),

            I\Section::make('ملاحظات داخلية')
                ->description('لفريق Fluent فقط — لا تظهر للجهة أبدًا.')
                ->schema([
                    I\TextEntry::make('internal_notes')->label('')->placeholder('لا توجد ملاحظات بعد.')->prose()->columnSpanFull(),
                ])->collapsible(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessChallenges::route('/'),
            'view' => Pages\ViewBusinessChallenge::route('/{record}'),
        ];
    }
}
