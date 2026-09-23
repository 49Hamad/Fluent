<?php

namespace App\Filament\Resources;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\EnrollmentResource\Pages;
use App\Filament\Resources\EnrollmentResource\RelationManagers;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\PaymentReceipt;
use App\Services\EnrollmentService;
use App\Services\WorkflowException;
use Filament\Forms;
use Filament\Infolists\Components as I;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * السداد وتأكيد المقاعد — one row per preliminarily-accepted application.
 * Agreement, media consent, bank-transfer receipt, payment verification and
 * the explicit seat confirmation (the only way to «مقبول نهائيًا»).
 * Shield permissions: …_enrollment.
 */
class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'السداد وتأكيد المقاعد';
    protected static ?string $modelLabel = 'مسار تأكيد مقعد';
    protected static ?string $pluralModelLabel = 'السداد وتأكيد المقاعد';
    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $n = static::getModel()::where('payment_status', PaymentStatus::ReceiptUploaded)->count();

        return $n ? (string) $n : null;   // receipts waiting for review
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'إيصالات بانتظار المراجعة';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['application.cohort', 'application.agreementAcceptance', 'application.latestMediaConsent', 'latestReceipt']);
    }

    /* ------------------------------------------------------------------
       List
       ------------------------------------------------------------------ */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('application.full_name')->label('المتقدم')->weight('bold')
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHas('application',
                        fn (Builder $query) => $query->where('full_name', 'like', "%{$search}%")
                            ->orWhere('reference', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                    ->description(fn (Enrollment $r) => $r->application?->reference),
                Tables\Columns\TextColumn::make('application.cohort.name')->label('الدفعة')->toggleable(),
                Tables\Columns\TextColumn::make('amount')->label('المبلغ المطلوب')
                    ->getStateUsing(fn (Enrollment $r) => $r->amount ?? $r->application?->cohort?->fee_amount)
                    ->formatStateUsing(fn ($state) => $state !== null ? EnrollmentService::money($state) : '—'),
                Tables\Columns\IconColumn::make('agreement')->label('الاتفاقية')->boolean()
                    ->getStateUsing(fn (Enrollment $r) => (bool) $r->application?->agreementAcceptance)
                    ->tooltip(fn (Enrollment $r) => $r->application?->agreementAcceptance?->accepted_at?->format('Y-m-d H:i')),
                Tables\Columns\TextColumn::make('media')->label('الاستخدام الإعلامي')->badge()
                    ->getStateUsing(fn (Enrollment $r) => self::mediaLabel($r))
                    ->color(fn (Enrollment $r) => $r->application?->latestMediaConsent?->granted ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('payment_status')->label('حالة السداد')->badge(),
                Tables\Columns\TextColumn::make('latestReceipt.created_at')->label('رفع الإيصال')->dateTime('Y-m-d H:i')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('payment_verified_at')->label('التحقق من السداد')->dateTime('Y-m-d H:i')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('seat_confirmed_at')->label('تأكيد المقعد')->dateTime('Y-m-d H:i')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cohort')->label('الدفعة')
                    ->options(fn () => Cohort::orderByDesc('id')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null,
                        fn (Builder $query, $id) => $query->whereHas('application', fn (Builder $query) => $query->where('cohort_id', $id)))),
                Tables\Filters\SelectFilter::make('payment_status')->label('حالة السداد')->options(PaymentStatus::class)->multiple(),
                Tables\Filters\TernaryFilter::make('agreement')->label('وافق على الاتفاقية')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('application.agreementAcceptance'),
                        false: fn (Builder $query) => $query->whereDoesntHave('application.agreementAcceptance'),
                    ),
                Tables\Filters\TernaryFilter::make('media')->label('موافق على الاستخدام الإعلامي')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('application.latestMediaConsent', fn (Builder $query) => $query->where('granted', true)),
                        false: fn (Builder $query) => $query->whereDoesntHave('application.latestMediaConsent', fn (Builder $query) => $query->where('granted', true)),
                    ),
                Tables\Filters\TernaryFilter::make('seat')->label('المقعد مؤكد')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('seat_confirmed_at'),
                        false: fn (Builder $query) => $query->whereNull('seat_confirmed_at'),
                    ),
            ])
            ->filtersFormColumns(2)
            ->actions([Tables\Actions\ViewAction::make()->label('عرض')]);
    }

    public static function mediaLabel(Enrollment $r): string
    {
        return $r->application?->latestMediaConsent?->granted
            ? 'موافق على الاستخدام الإعلامي'
            : 'غير موافق على الاستخدام الإعلامي';
    }

    /* ------------------------------------------------------------------
       Record page
       ------------------------------------------------------------------ */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            I\Section::make('المتقدم')->schema([
                I\TextEntry::make('application.full_name')->label('الاسم')->weight('bold'),
                I\TextEntry::make('application.reference')->label('رقم الطلب')->fontFamily('mono')->copyable()
                    ->url(fn (Enrollment $r) => auth()->user()?->can('view', $r->application)
                        ? StudentApplicationResource::getUrl('view', ['record' => $r->application]) : null),
                I\TextEntry::make('application.cohort.name')->label('الدفعة'),
                I\TextEntry::make('application.status')->label('حالة الطلب')->badge(),
                I\TextEntry::make('application.email')->label('البريد')->copyable(),
                I\TextEntry::make('application.phone')->label('الجوال')->copyable(),
            ])->columns(3),

            I\Section::make('الاتفاقية والموافقة الإعلامية')->schema([
                I\TextEntry::make('agreement_state')->label('اتفاقية المشاركة')->badge()
                    ->getStateUsing(fn (Enrollment $r) => $r->application?->agreementAcceptance ? 'تمت الموافقة' : 'لم يوافق بعد')
                    ->color(fn (Enrollment $r) => $r->application?->agreementAcceptance ? 'success' : 'gray'),
                I\TextEntry::make('application.agreementAcceptance.accepted_at')->label('تاريخ الموافقة')->dateTime('Y-m-d H:i')->placeholder('—'),
                I\TextEntry::make('agreement_version')->label('النسخة')->placeholder('—')
                    ->getStateUsing(fn (Enrollment $r) => ($a = $r->application?->agreementAcceptance) ? $a->agreement_title . ' — ' . $a->agreement_version : null),
                I\TextEntry::make('media_state')->label('الاستخدام الإعلامي')->badge()
                    ->getStateUsing(fn (Enrollment $r) => self::mediaLabel($r))
                    ->color(fn (Enrollment $r) => $r->application?->latestMediaConsent?->granted ? 'success' : 'gray')
                    ->helperText(fn (Enrollment $r) => ($m = $r->application?->latestMediaConsent)
                        ? 'آخر قرار: ' . $m->decided_at->format('Y-m-d H:i')
                        : 'لم يختر الطالب بعد (يُعامل كعدم موافقة).'),
                I\TextEntry::make('media_history')->label('سجل الموافقة الإعلامية')->columnSpan(2)
                    ->getStateUsing(fn (Enrollment $r) => $r->application?->mediaConsents
                        ->map(fn ($m) => $m->decided_at->format('Y-m-d H:i') . ' — ' . ($m->granted ? 'موافق' : 'غير موافق') . ' (' . $m->consent_version . ')')
                        ->implode(' · ') ?: '—'),
            ])->columns(3),

            I\Section::make('السداد')->schema([
                I\TextEntry::make('payment_status')->label('حالة السداد')->badge(),
                I\TextEntry::make('amount')->label('المبلغ المطلوب')
                    ->getStateUsing(fn (Enrollment $r) => $r->amount ?? $r->application?->cohort?->fee_amount)
                    ->formatStateUsing(fn ($state) => $state !== null ? EnrollmentService::money($state) : '—'),
                I\TextEntry::make('paymentMethod.name')->label('حساب التحويل')->placeholder('—'),
                I\TextEntry::make('latestReceipt.created_at')->label('آخر إيصال')->dateTime('Y-m-d H:i')->placeholder('لم يُرفع بعد'),
                I\TextEntry::make('payment_verified_at')->label('التحقق من السداد')->dateTime('Y-m-d H:i')->placeholder('—')
                    ->helperText(fn (Enrollment $r) => $r->verifier ? 'بواسطة ' . $r->verifier->name : null),
                I\TextEntry::make('reupload_reason')->label('سبب طلب إعادة الرفع (يراه الطالب)')->placeholder('—')
                    ->visible(fn (Enrollment $r) => filled($r->reupload_reason)),
            ])->columns(3),

            I\Section::make('تأكيد المقعد')->schema([
                I\TextEntry::make('seat_state')->label('المقعد')->badge()
                    ->getStateUsing(fn (Enrollment $r) => $r->seat_confirmed_at ? 'مؤكد' : 'غير مؤكد')
                    ->color(fn (Enrollment $r) => $r->seat_confirmed_at ? 'success' : 'gray'),
                I\TextEntry::make('seat_confirmed_at')->label('تاريخ التأكيد')->dateTime('Y-m-d H:i')->placeholder('—'),
                I\TextEntry::make('seatConfirmer.name')->label('أكّده')->placeholder('—'),
            ])->columns(3),

            I\Section::make('ملاحظات داخلية')->description('لفريق Fluent فقط — لا تظهر للطالب أبدًا.')->schema([
                I\TextEntry::make('internal_notes')->hiddenLabel()->placeholder('لا توجد ملاحظات.')->prose()->columnSpanFull(),
            ])->collapsible(),
        ]);
    }

    /* ------------------------------------------------------------------
       Receipt download (private, authorised employees only)
       ------------------------------------------------------------------ */
    public static function downloadReceipt(PaymentReceipt $receipt): ?StreamedResponse
    {
        abort_unless(auth()->user()?->can('view', $receipt->enrollment), 403);
        $disk = Storage::disk(PaymentReceipt::DISK);
        if (! $disk->exists($receipt->file_path)) {
            Notification::make()->danger()->title('ملف الإيصال غير موجود')->send();
            return null;
        }
        $ext = pathinfo($receipt->file_path, PATHINFO_EXTENSION);
        $app = $receipt->enrollment->application;

        return $disk->download($receipt->file_path, 'إيصال - ' . $app->full_name . ' - ' . $app->reference . '.' . $ext);
    }

    /** Runs a workflow action and shows the result (rule messages are safe Arabic text). */
    public static function run(callable $fn, string $ok): void
    {
        try {
            $fn();
            Notification::make()->success()->title($ok)->send();
        } catch (WorkflowException $e) {
            if ($e->reason === 'mail_failed') {
                Notification::make()->warning()->title($ok)->body($e->getMessage())->persistent()->send();
                return;
            }
            Notification::make()->danger()->title('لم يتم الإجراء')->body($e->getMessage())->send();
        }
    }

    public static function canConfirmSeat(Enrollment $r): bool
    {
        return $r->payment_status === PaymentStatus::Verified
            && ! $r->seat_confirmed_at
            && $r->application?->status === ApplicationStatus::PreliminaryAccepted
            && (bool) $r->application?->agreementAcceptance;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReceiptsRelationManager::class,
            RelationManagers\EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'view' => Pages\ViewEnrollment::route('/{record}'),
        ];
    }
}
