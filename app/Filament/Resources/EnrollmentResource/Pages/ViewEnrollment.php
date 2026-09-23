<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\EnrollmentResource;
use App\Services\EnrollmentService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEnrollment extends ViewRecord
{
    protected static string $resource = EnrollmentResource::class;

    public function getTitle(): string
    {
        return $this->record->application?->full_name ?? 'مسار تأكيد مقعد';
    }

    protected function getHeaderActions(): array
    {
        $can = fn () => auth()->user()?->can('update', $this->record);
        $status = fn () => $this->record->payment_status;
        $service = fn () => app(EnrollmentService::class);
        $refresh = fn () => $this->record->refresh();

        return [
            Actions\Action::make('receipt')
                ->label('مراجعة الإيصال')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('gray')
                ->visible(fn () => (bool) $this->record->latestReceipt)
                ->action(fn () => EnrollmentResource::downloadReceipt($this->record->latestReceipt)),

            Actions\Action::make('under_review')
                ->label('قيد التحقق')
                ->icon('heroicon-o-magnifying-glass')
                ->color('info')
                ->visible(fn () => $can() && $status() === PaymentStatus::ReceiptUploaded)
                ->requiresConfirmation()
                ->modalHeading('نقل السداد إلى «قيد التحقق»؟')
                ->modalDescription('يرى الطالب أن إيصاله قيد التحقق.')
                ->action(function () use ($service, $refresh) {
                    EnrollmentResource::run(fn () => $service()->markUnderReview($this->record, auth()->user()), 'تم تحديث حالة السداد');
                    $refresh();
                }),

            Actions\Action::make('verify')
                ->label('تأكيد التحقق من السداد')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $can() && in_array($status(), [PaymentStatus::ReceiptUploaded, PaymentStatus::UnderReview], true))
                ->requiresConfirmation()
                ->modalHeading('تأكيد وصول المبلغ؟')
                ->modalDescription('تأكد من وصول المبلغ كاملًا إلى الحساب قبل المتابعة. هذا لا يؤكد المقعد — تأكيد المقعد خطوة منفصلة.')
                ->modalSubmitActionLabel('تم التحقق من السداد')
                ->action(function () use ($service, $refresh) {
                    EnrollmentResource::run(fn () => $service()->verifyPayment($this->record, auth()->user()), 'تم التحقق من السداد');
                    $refresh();
                }),

            Actions\Action::make('reupload')
                ->label('طلب إعادة رفع الإيصال')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->visible(fn () => $can() && ! $this->record->seat_confirmed_at
                    && in_array($status(), [PaymentStatus::ReceiptUploaded, PaymentStatus::UnderReview, PaymentStatus::Verified], true))
                ->form([
                    Forms\Components\Textarea::make('reason')->label('السبب (يظهر للطالب)')->required()->rows(3)->maxLength(500)
                        ->placeholder('مثال: الإيصال غير واضح، أو المبلغ غير مطابق.'),
                    Forms\Components\Toggle::make('notify')->label('إبلاغ الطالب بالبريد')->default(true),
                ])
                ->modalHeading('طلب إعادة رفع الإيصال')
                ->modalSubmitActionLabel('إرسال الطلب')
                ->action(function (array $data) use ($service, $refresh) {
                    EnrollmentResource::run(fn () => $service()->requestReupload($this->record, auth()->user(), $data['reason'], (bool) $data['notify']),
                        'تم طلب إعادة رفع الإيصال');
                    $refresh();
                }),

            Actions\Action::make('confirm_seat')
                ->label('تأكيد المقعد (قبول نهائي)')
                ->icon('heroicon-o-academic-cap')
                ->color('primary')
                ->visible(fn () => $can() && EnrollmentResource::canConfirmSeat($this->record))
                ->form([
                    Forms\Components\Placeholder::make('summary')->hiddenLabel()->content(fn () =>
                        'الطالب: ' . $this->record->application->full_name . ' — ' . $this->record->application->reference .
                        ' · الدفعة: ' . ($this->record->application->cohort?->name ?? '—') .
                        ' · الاتفاقية: تمت الموافقة · السداد: تم التحقق'),
                    Forms\Components\Toggle::make('notify')->label('إبلاغ الطالب بالبريد (بريد القبول النهائي مع تفاصيل الدفعة)')->default(true),
                ])
                ->modalHeading('تأكيد مقعد الطالب؟')
                ->modalDescription('سيصبح الطلب «مقبول نهائيًا» وتظهر للطالب تفاصيل دفعته. يُسجَّل اسمك ووقت التأكيد.')
                ->modalSubmitActionLabel('نعم، أكّد المقعد')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->action(function (array $data) use ($service, $refresh) {
                    EnrollmentResource::run(function () use ($service, $data) {
                        $r = $service()->confirmSeat($this->record, auth()->user(), (bool) $data['notify']);
                        if (! empty($r['email_failed'])) {
                            throw new \App\Services\WorkflowException('تم تأكيد المقعد، لكن تعذّر إرسال البريد — تحقّق من إعدادات البريد.', 'mail_failed');
                        }
                    }, 'تم تأكيد المقعد — الطلب مقبول نهائيًا');
                    $refresh();
                }),

            Actions\Action::make('notes')
                ->label('ملاحظات داخلية')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible($can)
                ->fillForm(fn () => ['internal_notes' => $this->record->internal_notes])
                ->form([
                    Forms\Components\Textarea::make('internal_notes')->label('الملاحظات')->rows(6)->maxLength(5000)
                        ->helperText('لفريق Fluent فقط — لا تظهر للطالب أبدًا.'),
                ])
                ->action(function (array $data) {
                    abort_unless(auth()->user()?->can('update', $this->record), 403);
                    $this->record->forceFill(['internal_notes' => $data['internal_notes']])->save();
                    Notification::make()->success()->title('تم حفظ الملاحظات')->send();
                }),
        ];
    }
}
