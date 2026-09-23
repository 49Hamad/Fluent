<?php

namespace App\Filament\Resources;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\StudentApplicationResource\Pages;
use App\Filament\Resources\StudentApplicationResource\RelationManagers\StatusChangesRelationManager;
use App\Models\Cohort;
use App\Models\StudentApplication;
use App\Services\StudentApplicationStatusService;
use App\Support\StudentApplicationForm as Q;
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
 * طلبات الطلاب — review and manage real applications.
 * Applications are created only by the public form (no "create" here).
 */
class StudentApplicationResource extends Resource
{
    protected static ?string $model = StudentApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'طلبات الطلاب';
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'طلبات الطلاب';
    protected static ?string $recordTitleAttribute = 'full_name';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $n = static::getModel()::where('status', ApplicationStatus::Received)->count();

        return $n ? (string) $n : null;   // new (not yet reviewed) applications
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'طلبات جديدة لم تُراجع';
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with('cohort'))
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('رقم الطلب')->searchable()->copyable()
                    ->fontFamily('mono')->size('sm'),
                Tables\Columns\TextColumn::make('full_name')->label('الاسم')->searchable()->weight('bold')
                    ->description(fn (StudentApplication $r) => $r->university),
                Tables\Columns\TextColumn::make('email')->label('البريد')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')->label('الجوال')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cohort.name')->label('الدفعة')->toggleable(),
                Tables\Columns\TextColumn::make('study_status')->label('الحالة الدراسية')
                    ->formatStateUsing(fn ($state) => Q::label(Q::STUDY_STATUS, $state))->toggleable(),
                Tables\Columns\TextColumn::make('weekly_commitment')->label('الالتزام الأسبوعي')
                    ->formatStateUsing(fn ($state) => Q::label(Q::WEEKLY_COMMITMENT, $state))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\IconColumn::make('submitted_via_waitlist')->label('قائمة انتظار')->boolean()
                    ->trueIcon('heroicon-o-queue-list')->falseIcon('')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ التقديم')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cohort_id')->label('الدفعة')
                    ->options(fn () => Cohort::orderByDesc('id')->pluck('name', 'id')->all()),
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options(ApplicationStatus::class)->multiple(),
                Tables\Filters\SelectFilter::make('study_status')->label('الحالة الدراسية')->options(Q::STUDY_STATUS),
                Tables\Filters\SelectFilter::make('gender')->label('الجنس')->options(Q::GENDER),
                Tables\Filters\SelectFilter::make('weekly_commitment')->label('الالتزام الأسبوعي')->options(Q::WEEKLY_COMMITMENT),
                Tables\Filters\TernaryFilter::make('has_experience')->label('لديه تجربة عملية سابقة'),
                Tables\Filters\TernaryFilter::make('submitted_via_waitlist')->label('قُدّم أثناء قائمة الانتظار'),
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
                    ->visible(fn () => auth()->user()?->can('update_student::application'))
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data) {
                        $result = ['changed' => 0, 'emailed' => 0, 'failed' => 0];
                        foreach ($records as $record) {
                            $r = app(StudentApplicationStatusService::class)->change(
                                $record, ApplicationStatus::from($data['status']), auth()->user(),
                                (bool) ($data['notify_student'] ?? false), $data['message_to_student'] ?? null
                            );
                            $result['changed'] += (int) $r['changed'];
                            $result['emailed'] += (int) $r['emailed'];
                            $result['failed'] += (int) $r['email_failed'];
                        }
                        self::reportResult($result['changed'], $result['emailed'], $result['failed']);
                    }),
                Tables\Actions\BulkAction::make('export_selected')
                    ->label('تصدير المحدد (Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Collection $records) => self::exportCsv($records)),
            ]);
    }

    /* ------------------------------------------------------------------
       Status change (single + bulk share the same form and service)
       ------------------------------------------------------------------ */
    public static function statusFormSchema(?StudentApplication $record = null): array
    {
        return [
            Forms\Components\Select::make('status')
                ->label('الحالة الجديدة')
                ->options(ApplicationStatus::class)
                ->default($record?->status?->value)
                ->required()
                ->native(false),
            Forms\Components\Toggle::make('notify_student')
                ->label('إبلاغ الطالب بالبريد')
                ->helperText('لن يُرسل أي بريد إلا إذا فعّلت هذا الخيار.')
                ->default(false)
                ->live(),
            Forms\Components\Textarea::make('message_to_student')
                ->label('رسالة إضافية للطالب (اختياري)')
                ->helperText('تظهر في البريد تحت نص الحالة.')
                ->rows(3)->maxLength(1000)
                ->visible(fn (Forms\Get $get) => (bool) $get('notify_student')),
        ];
    }

    public static function statusTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('change_status')
            ->label('تغيير الحالة')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->visible(fn (StudentApplication $record) => auth()->user()?->can('update', $record))
            ->form(fn (StudentApplication $record) => self::statusFormSchema($record))
            ->modalHeading(fn (StudentApplication $record) => 'تغيير حالة طلب ' . $record->full_name)
            ->modalSubmitActionLabel('حفظ الحالة')
            ->action(fn (StudentApplication $record, array $data) => self::applyStatus($record, $data));
    }

    public static function applyStatus(StudentApplication $record, array $data): void
    {
        $r = app(StudentApplicationStatusService::class)->change(
            $record, ApplicationStatus::from($data['status']), auth()->user(),
            (bool) ($data['notify_student'] ?? false), $data['message_to_student'] ?? null
        );
        self::reportResult((int) $r['changed'], (int) $r['emailed'], (int) $r['email_failed']);
    }

    private static function reportResult(int $changed, int $emailed, int $failed): void
    {
        $body = 'تم تحديث ' . $changed . ' طلب.';
        if ($emailed) $body .= ' أُرسل البريد إلى ' . $emailed . '.';
        if ($failed) {
            Notification::make()->warning()->title('تم حفظ الحالة، لكن تعذّر إرسال البريد')
                ->body($body . ' تعذّر الإرسال إلى ' . $failed . ' — تحقّق من إعدادات البريد.')->persistent()->send();
            return;
        }
        Notification::make()->success()->title('تم حفظ الحالة')->body($body)->send();
    }

    /* ------------------------------------------------------------------
       CV download (private file, authorised employees only)
       ------------------------------------------------------------------ */
    public static function downloadCv(StudentApplication $record): ?StreamedResponse
    {
        abort_unless(auth()->user()?->can('view', $record), 403);
        $disk = Storage::disk(StudentApplication::CV_DISK);
        if (! $record->cv_path || ! $disk->exists($record->cv_path)) {
            Notification::make()->danger()->title('ملف السيرة الذاتية غير موجود')->send();
            return null;
        }
        $name = 'CV - ' . $record->full_name . ' - ' . $record->reference . '.pdf';

        return $disk->download($record->cv_path, $name);
    }

    /* ------------------------------------------------------------------
       Export (CSV that opens in Excel with Arabic text)
       ------------------------------------------------------------------ */
    public static function exportCsv(iterable $records): StreamedResponse
    {
        $headers = ['رقم الطلب', 'الدفعة', 'الحالة', 'تاريخ التقديم', 'الاسم الثلاثي', 'البريد', 'الجوال', 'الجنس', 'المدينة',
            'الجامعة / الجهة التعليمية', 'التخصص', 'الحالة الدراسية', 'سنة التخرج', 'ليش تبي تدخل التجربة', 'ما ينقصه قبل بيئة العمل',
            'تجربة عملية سابقة', 'تفاصيل التجربة', 'سؤال الفريق', 'الالتزام الأسبوعي', 'LinkedIn', 'Portfolio', 'قائمة انتظار'];

        return response()->streamDownload(function () use ($records, $headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // UTF-8 BOM for Excel
            fputcsv($out, $headers);
            foreach ($records as $r) {
                $r->loadMissing('cohort');
                fputcsv($out, [
                    $r->reference, $r->cohort?->name, $r->status?->getLabel(), $r->created_at?->format('Y-m-d H:i'),
                    $r->full_name, $r->email, $r->phone, Q::label(Q::GENDER, $r->gender), $r->city,
                    $r->university, $r->major, Q::label(Q::STUDY_STATUS, $r->study_status), $r->graduation_year,
                    $r->motivation, self::gapsText($r), $r->has_experience ? 'نعم' : 'لا', $r->experience_details,
                    $r->team_scenario, Q::label(Q::WEEKLY_COMMITMENT, $r->weekly_commitment), $r->linkedin_url, $r->portfolio_url,
                    $r->submitted_via_waitlist ? 'نعم' : 'لا',
                ]);
            }
            fclose($out);
        }, 'fluent-applications-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function gapsText(StudentApplication $r): string
    {
        return collect($r->gaps ?? [])
            ->map(fn ($k) => $k === 'other' && $r->gaps_other ? 'أخرى: ' . $r->gaps_other : Q::label(Q::GAPS, $k))
            ->implode('، ');
    }

    /* ------------------------------------------------------------------
       Application page (readable — no raw data)
       ------------------------------------------------------------------ */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            I\Section::make()->schema([
                I\TextEntry::make('reference')->label('رقم الطلب')->copyable()->fontFamily('mono')->weight('bold'),
                I\TextEntry::make('status')->label('الحالة')->badge(),
                I\TextEntry::make('cohort.name')->label('الدفعة'),
                I\TextEntry::make('created_at')->label('تاريخ التقديم')->dateTime('Y-m-d H:i'),
                I\IconEntry::make('submitted_via_waitlist')->label('قُدّم أثناء قائمة الانتظار')->boolean(),
                I\TextEntry::make('status_changed_at')->label('آخر تغيير للحالة')->since(),
            ])->columns(3),

            I\Section::make('بياناته')->schema([
                I\TextEntry::make('full_name')->label('الاسم الثلاثي'),
                I\TextEntry::make('email')->label('البريد الإلكتروني')->copyable(),
                I\TextEntry::make('phone')->label('رقم الجوال')->copyable(),
                I\TextEntry::make('gender')->label('الجنس')->formatStateUsing(fn ($state) => Q::label(Q::GENDER, $state)),
                I\TextEntry::make('city')->label('المدينة'),
                I\TextEntry::make('university')->label('الجامعة / الجهة التعليمية'),
                I\TextEntry::make('major')->label('التخصص'),
                I\TextEntry::make('study_status')->label('الحالة الدراسية')->formatStateUsing(fn ($state) => Q::label(Q::STUDY_STATUS, $state)),
                I\TextEntry::make('graduation_year')->label('سنة التخرج (المتوقعة)'),
            ])->columns(3),

            I\Section::make('إجاباته')->schema([
                I\TextEntry::make('motivation')->label('ليش تبي تدخل تجربة Fluent؟')->columnSpanFull()->prose(),
                I\TextEntry::make('gaps')->label('وش أكثر شيء تحس ينقصك قبل دخول بيئة العمل؟')->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => Q::label(Q::GAPS, $state)),
                I\TextEntry::make('gaps_other')->label('«أخرى»')->visible(fn (StudentApplication $r) => filled($r->gaps_other)),
                I\TextEntry::make('has_experience')->label('هل سبق اشتغل على مشروع حقيقي أو تجربة عملية؟')
                    ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا'),
                I\TextEntry::make('experience_details')->label('عن التجربة')->columnSpanFull()->prose()
                    ->visible(fn (StudentApplication $r) => $r->has_experience),
                I\TextEntry::make('team_scenario')->label('لو أحد أعضاء الفريق ما أنجز الجزء المطلوب منه قبل التسليم، وش بيسوي؟')
                    ->columnSpanFull()->prose(),
                I\TextEntry::make('weekly_commitment')->label('الالتزام الأسبوعي')
                    ->formatStateUsing(fn ($state) => Q::label(Q::WEEKLY_COMMITMENT, $state)),
            ])->columns(2),

            I\Section::make('الروابط والسيرة الذاتية')->schema([
                I\TextEntry::make('linkedin_url')->label('LinkedIn')->url(fn ($state) => $state, true)->placeholder('—')->color('primary'),
                I\TextEntry::make('portfolio_url')->label('Portfolio / أعمال سابقة')->url(fn ($state) => $state, true)->placeholder('—')->color('primary'),
                I\TextEntry::make('cv_original_name')->label('السيرة الذاتية')
                    ->suffix(fn (StudentApplication $r) => ' · ' . ($r->cv_size >= 1048576
                        ? number_format($r->cv_size / 1048576, 1) . ' MB'
                        : max(1, round($r->cv_size / 1024)) . ' KB'))
                    ->helperText('للتحميل استخدم زر «تحميل السيرة الذاتية» أعلى الصفحة.'),
                I\TextEntry::make('consented_at')->label('وافق على استخدام البيانات')->dateTime('Y-m-d H:i'),
            ])->columns(2),

            I\Section::make('المقابلة')->schema([
                I\TextEntry::make('interview_at')->label('الموعد')->dateTime('Y-m-d H:i')->placeholder('لم تُحدّد'),
                I\TextEntry::make('interview_mode')->label('نوعها')->placeholder('—')
                    ->formatStateUsing(fn ($state) => StudentApplication::INTERVIEW_MODES[$state] ?? $state),
                I\TextEntry::make('interview_location')->label('المكان / الرابط')->placeholder('—'),
                I\TextEntry::make('interview_note')->label('ملاحظة للطالب')->placeholder('—')->columnSpanFull(),
            ])->columns(3)->collapsible(),

            I\Section::make('ملاحظات داخلية')
                ->description('لفريق Fluent فقط — لا تظهر للطالب أبدًا.')
                ->schema([
                    I\TextEntry::make('internal_notes')->label('')->placeholder('لا توجد ملاحظات بعد.')->prose()->columnSpanFull(),
                ])->collapsible(),
        ]);
    }

    public static function getRelations(): array
    {
        return [StatusChangesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentApplications::route('/'),
            'view' => Pages\ViewStudentApplication::route('/{record}'),
        ];
    }
}
