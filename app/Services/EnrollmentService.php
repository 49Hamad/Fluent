<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\EnrollmentResource;
use App\Mail\PaymentReuploadMail;
use App\Models\Agreement;
use App\Models\AgreementAcceptance;
use App\Models\Enrollment;
use App\Models\MediaConsent;
use App\Models\PaymentReceipt;
use App\Models\StudentApplication;
use App\Models\User;
use App\Support\EnrollmentTexts;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The ONE place where the seat-confirmation workflow changes:
 *
 *   مقبول مبدئيًا → agreement → (optional media consent) → bank transfer
 *   → receipt → payment verification → explicit seat confirmation → مقبول نهائيًا
 *
 * Payment progress is kept on the Enrollment, never on the application status.
 * Only confirmSeat() final-accepts, and only through StudentApplicationStatusService.
 */
class EnrollmentService
{
    /* ==================================================================
       Opening the track
       ================================================================== */

    /** Creates the track once (when the application becomes «مقبول مبدئيًا»). */
    public function open(StudentApplication $application): Enrollment
    {
        $enrollment = Enrollment::firstOrCreate(
            ['student_application_id' => $application->id],
            ['payment_status' => PaymentStatus::AwaitingTransfer, 'payment_status_changed_at' => now()]
        );

        if ($enrollment->wasRecentlyCreated) {
            $this->log($enrollment, 'enrollment_opened', 'system');
        }

        return $enrollment;
    }

    /** Workflow is available to the student only in these application statuses. */
    public static function visibleFor(StudentApplication $application): bool
    {
        return in_array($application->status, [ApplicationStatus::PreliminaryAccepted, ApplicationStatus::FinalAccepted], true);
    }

    /* ==================================================================
       What the student is asked to accept
       ================================================================== */

    public function agreementFor(StudentApplication $application): ?Agreement
    {
        return Agreement::activeFor($application->cohort);
    }

    /** Everything needed before the student may accept: agreement + fee + active bank details. */
    public function missingConfiguration(StudentApplication $application): array
    {
        $cohort = $application->cohort;
        $missing = [];
        if (! $this->agreementFor($application)) $missing[] = 'agreement';
        if (! $cohort || $cohort->fee_amount === null) $missing[] = 'fee';
        if (! $cohort?->paymentMethod || ! $cohort->paymentMethod->is_active) $missing[] = 'payment_method';

        return $missing;
    }

    /** Cohort + fee details shown with (and frozen into) the agreement. */
    public function details(StudentApplication $application): array
    {
        $c = $application->cohort;

        return [
            'party' => 'مؤسسة طلاقة للاتصالات وتقنية المعلومات (Fluent) — سجل تجاري 1131337184',
            'participant' => $application->full_name,
            'reference' => $application->reference,
            'cohort' => $c?->name,
            'start_date' => $c?->start_date?->locale('ar')->translatedFormat('l j F Y'),
            'schedule' => $c?->schedule,
            'location' => $c?->location,
            'fee' => $c?->fee_amount !== null ? self::money($c->fee_amount, $c->fee_currency) : null,
            'payment_method' => 'تحويل بنكي',
            'payment_deadline' => $c?->payment_deadline?->locale('ar')->translatedFormat('j F Y'),
        ];
    }

    public static function hash(string $content, array $details): string
    {
        return hash('sha256', $content . "\n" . json_encode($details, JSON_UNESCAPED_UNICODE));
    }

    public static function money($amount, ?string $currency = 'SAR'): string
    {
        $n = number_format((float) $amount, 2);
        if (str_ends_with($n, '.00')) $n = substr($n, 0, -3);

        return $n . ' ' . (($currency ?: 'SAR') === 'SAR' ? 'ريال سعودي' : $currency);
    }

    /* ==================================================================
       Student actions
       ================================================================== */

    /**
     * @throws WorkflowException
     */
    public function acceptAgreement(StudentApplication $application, int $agreementId, string $hash, ?string $ip, ?string $userAgent): AgreementAcceptance
    {
        $this->requirePreliminary($application);
        if ($application->agreementAcceptance()->exists()) {
            throw new WorkflowException('تمت الموافقة على الاتفاقية مسبقًا.');
        }
        if ($this->missingConfiguration($application)) {
            throw new WorkflowException('الاتفاقية أو تفاصيل السداد قيد الإعداد. حاول لاحقًا.');
        }

        $agreement = $this->agreementFor($application);
        $details = $this->details($application);
        $expected = self::hash($agreement->content, $details);

        // The student must accept exactly the version and text they were shown.
        if ($agreement->id !== $agreementId || ! hash_equals($expected, $hash)) {
            throw new WorkflowException('تم تحديث الاتفاقية أو بيانات الدفعة. راجعها مرة أخرى قبل الموافقة.', 'agreement_changed');
        }

        return DB::transaction(function () use ($application, $agreement, $details, $expected, $ip, $userAgent) {
            $acceptance = AgreementAcceptance::create([
                'student_application_id' => $application->id,
                'agreement_id' => $agreement->id,
                'agreement_title' => $agreement->title,
                'agreement_version' => $agreement->version,
                'content_snapshot' => $agreement->content,
                'details_snapshot' => $details,
                'content_hash' => $expected,
                'acceptance_statement' => EnrollmentTexts::ACCEPTANCE_STATEMENT,
                'accepted_at' => now(),
                'ip_address' => $ip,
                'user_agent' => Str::limit((string) $userAgent, 490, ''),
            ]);

            // Freeze what the student has to pay at the moment of acceptance.
            $cohort = $application->cohort;
            $enrollment = $this->open($application);
            $enrollment->update([
                'amount' => $cohort->fee_amount,
                'currency' => $cohort->fee_currency ?: 'SAR',
                'payment_method_id' => $cohort->payment_method_id,
                'payment_method_type' => $cohort->paymentMethod->type,
            ]);
            $this->log($enrollment, 'agreement_accepted', 'student', null, null, null,
                $agreement->title . ' — ' . $agreement->version);

            return $acceptance;
        });
    }

    public function setMediaConsent(StudentApplication $application, bool $granted, ?string $ip, ?string $userAgent): MediaConsent
    {
        if (! self::visibleFor($application)) {
            throw new WorkflowException('هذه الخطوة غير متاحة لطلبك حاليًا.');
        }

        return DB::transaction(function () use ($application, $granted, $ip, $userAgent) {
            $consent = MediaConsent::create([
                'student_application_id' => $application->id,
                'granted' => $granted,
                'consent_text' => EnrollmentTexts::MEDIA_CONSENT_TEXT,
                'consent_version' => EnrollmentTexts::MEDIA_CONSENT_VERSION,
                'decided_at' => now(),
                'ip_address' => $ip,
                'user_agent' => Str::limit((string) $userAgent, 490, ''),
            ]);
            $this->log($this->open($application), $granted ? 'media_granted' : 'media_declined', 'student');

            return $consent;
        });
    }

    public function uploadReceipt(StudentApplication $application, UploadedFile $file): PaymentReceipt
    {
        $this->requirePreliminary($application);
        if (! $application->agreementAcceptance()->exists()) {
            throw new WorkflowException('وافق على الاتفاقية أولًا.');
        }
        $enrollment = $this->open($application);
        if (! $enrollment->payment_status->acceptsReceipt()) {
            throw new WorkflowException('تم استلام إيصالك وهو قيد المراجعة. لا حاجة لرفعه مرة أخرى.');
        }

        $mime = $file->getMimeType();
        $ext = EnrollmentTexts::RECEIPT_MIMES[$mime] ?? null;
        if (! $ext) {
            throw new WorkflowException('صيغة الملف غير مقبولة.');
        }

        $path = $file->storeAs(now()->format('Y/m'), Str::random(40) . '.' . $ext, PaymentReceipt::DISK);

        try {
            $receipt = DB::transaction(function () use ($enrollment, $file, $path, $mime) {
                $receipt = $enrollment->receipts()->create([
                    'file_path' => $path,
                    'original_name' => Str::limit($file->getClientOriginalName(), 240, ''),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                ]);
                $this->transition($enrollment, PaymentStatus::ReceiptUploaded, 'receipt_uploaded', 'student', null,
                    ['reupload_reason' => null]);

                return $receipt;
            });
        } catch (\Throwable $e) {
            Storage::disk(PaymentReceipt::DISK)->delete($path);
            throw $e;
        }

        $this->notifyTeam($enrollment);

        return $receipt;
    }

    /* ==================================================================
       Employee actions (Filament) — permission is checked here too
       ================================================================== */

    public function markUnderReview(Enrollment $enrollment, User $by): void
    {
        $this->authorize($by, $enrollment);
        $this->requireStatus($enrollment, [PaymentStatus::ReceiptUploaded]);
        $this->transition($enrollment, PaymentStatus::UnderReview, 'under_review', 'staff', $by);
    }

    public function verifyPayment(Enrollment $enrollment, User $by): void
    {
        $this->authorize($by, $enrollment);
        $this->requireStatus($enrollment, [PaymentStatus::ReceiptUploaded, PaymentStatus::UnderReview]);

        DB::transaction(function () use ($enrollment, $by) {
            $enrollment->latestReceipt?->update(['review_status' => 'accepted', 'reviewed_at' => now(), 'reviewed_by' => $by->id]);
            $this->transition($enrollment, PaymentStatus::Verified, 'payment_verified', 'staff', $by, [
                'payment_verified_at' => now(),
                'payment_verified_by' => $by->id,
            ]);
        });
    }

    /** @return bool whether the student was e-mailed */
    public function requestReupload(Enrollment $enrollment, User $by, string $reason, bool $notifyStudent): bool
    {
        $this->authorize($by, $enrollment);
        $this->requireStatus($enrollment, [PaymentStatus::ReceiptUploaded, PaymentStatus::UnderReview, PaymentStatus::Verified]);
        if ($enrollment->seat_confirmed_at) {
            throw new WorkflowException('المقعد مؤكد بالفعل.');
        }
        $reason = trim($reason);

        DB::transaction(function () use ($enrollment, $by, $reason) {
            $enrollment->latestReceipt?->update(['review_status' => 'rejected', 'reviewed_at' => now(), 'reviewed_by' => $by->id]);
            $this->transition($enrollment, PaymentStatus::ReuploadRequested, 'reupload_requested', 'staff', $by, [
                'reupload_reason' => $reason,
                'payment_verified_at' => null,
                'payment_verified_by' => null,
            ], $reason);
        });

        if (! $notifyStudent) {
            return false;
        }
        try {
            $app = $enrollment->application()->with('cohort')->first();
            Mail::to($app->email)->send(new PaymentReuploadMail($app, $reason));

            return true;
        } catch (\Throwable $e) {
            Log::error('Fluent: re-upload e-mail failed', ['enrollment' => $enrollment->id, 'error' => $e->getMessage()]);
            throw new WorkflowException('تم حفظ الطلب، لكن تعذّر إرسال البريد — تحقّق من إعدادات البريد.', 'mail_failed');
        }
    }

    /**
     * Explicit seat confirmation → the application becomes «مقبول نهائيًا».
     * @return array result of StudentApplicationStatusService::change()
     */
    public function confirmSeat(Enrollment $enrollment, User $by, bool $notifyStudent): array
    {
        $this->authorize($by, $enrollment);
        $app = $enrollment->application()->with('cohort')->first();

        if ($app->status !== ApplicationStatus::PreliminaryAccepted) {
            throw new WorkflowException('تأكيد المقعد متاح فقط لطلب حالته «مقبول مبدئيًا».');
        }
        if (! $app->agreementAcceptance()->exists()) {
            throw new WorkflowException('لم يوافق الطالب على الاتفاقية بعد.');
        }
        if ($enrollment->payment_status !== PaymentStatus::Verified) {
            throw new WorkflowException('لا يمكن تأكيد المقعد قبل التحقق من السداد.');
        }
        if ($enrollment->seat_confirmed_at) {
            throw new WorkflowException('المقعد مؤكد بالفعل.');
        }

        DB::transaction(function () use ($enrollment, $by) {
            $enrollment->forceFill(['seat_confirmed_at' => now(), 'seat_confirmed_by' => $by->id])->save();
            $this->log($enrollment, 'seat_confirmed', 'staff', $by);
        });

        // The only path to «مقبول نهائيًا» — recorded in the application history with the employee.
        return app(StudentApplicationStatusService::class)->change(
            $app, ApplicationStatus::FinalAccepted, $by, $notifyStudent, null, seatConfirmation: true
        );
    }

    /* ==================================================================
       Helpers
       ================================================================== */

    private function requirePreliminary(StudentApplication $application): void
    {
        if ($application->status !== ApplicationStatus::PreliminaryAccepted) {
            throw new WorkflowException('هذه الخطوة متاحة فقط بعد القبول المبدئي.');
        }
    }

    private function requireStatus(Enrollment $enrollment, array $allowed): void
    {
        if (! in_array($enrollment->payment_status, $allowed, true)) {
            throw new WorkflowException('لا يمكن تنفيذ هذا الإجراء في حالة السداد الحالية («' . $enrollment->payment_status->getLabel() . '»).');
        }
    }

    private function authorize(User $by, Enrollment $enrollment): void
    {
        abort_unless($by->can('update', $enrollment), 403);
    }

    private function transition(Enrollment $enrollment, PaymentStatus $to, string $event, string $actor, ?User $by, array $extra = [], ?string $note = null): void
    {
        $from = $enrollment->payment_status;
        $enrollment->forceFill(array_merge($extra, ['payment_status' => $to, 'payment_status_changed_at' => now()]))->save();
        $this->log($enrollment, $event, $actor, $by, $from?->value, $to->value, $note);
    }

    private function log(Enrollment $enrollment, string $event, string $actor, ?User $by = null, ?string $from = null, ?string $to = null, ?string $note = null): void
    {
        $enrollment->events()->create([
            'event' => $event, 'actor' => $actor, 'user_id' => $by?->id,
            'from_status' => $from, 'to_status' => $to, 'note' => $note,
        ]);
    }

    /** Filament bell for employees allowed to manage payments. */
    private function notifyTeam(Enrollment $enrollment): void
    {
        try {
            $app = $enrollment->application;
            $notification = Notification::make()
                ->title('إيصال تحويل جديد')
                ->body($app->full_name . ' — ' . $app->reference)
                ->icon('heroicon-o-banknotes')
                ->warning()
                ->actions([
                    Action::make('open')->label('مراجعة الإيصال')->button()
                        ->url(EnrollmentResource::getUrl('view', ['record' => $enrollment])),
                    Action::make('markAsRead')->label('وضع علامة مقروء')->markAsRead(),
                ]);

            User::where('is_active', 1)->get()
                ->filter(fn (User $u) => $u->can('view_any_enrollment'))
                ->each(fn (User $u) => $u->notifyNow($notification->toDatabase()));
        } catch (\Throwable $e) {
            Log::warning('Fluent: receipt notification failed', ['error' => $e->getMessage()]);
        }
    }
}
