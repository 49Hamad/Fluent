<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Models\Agreement;
use App\Models\PaymentReceipt;
use App\Models\StudentApplication;
use App\Services\EnrollmentService;

/**
 * The seat-confirmation data a student may see — strict allow-list.
 * Never included: internal notes, employee names/ids, IP / user agent,
 * file paths, audit events, other students' data.
 */
class StudentEnrollmentData
{
    public static function for(StudentApplication $a): array
    {
        $service = app(EnrollmentService::class);
        $a->loadMissing(['cohort.paymentMethod', 'agreementAcceptance', 'latestMediaConsent']);
        $enrollment = $service->open($a);
        $enrollment->load(['receipts', 'paymentMethod']);

        $acceptance = $a->agreementAcceptance;
        $final = $a->status === ApplicationStatus::FinalAccepted;
        $missing = $acceptance ? [] : $service->missingConfiguration($a);

        // ---- Agreement: the frozen snapshot once accepted, otherwise the current active version
        if ($acceptance) {
            $agreement = [
                'accepted' => true,
                'title' => $acceptance->agreement_title,
                'version' => $acceptance->agreement_version,
                'html' => Agreement::renderContent($acceptance->content_snapshot),
                'details' => $acceptance->details_snapshot,
                'accepted_at' => self::when($acceptance->accepted_at),
                'statement' => $acceptance->acceptance_statement,
            ];
        } elseif (! $missing) {
            $current = $service->agreementFor($a);
            $details = $service->details($a);
            $agreement = [
                'accepted' => false,
                'id' => $current->id,
                'title' => $current->title,
                'version' => $current->version,
                'html' => Agreement::renderContent($current->content),
                'details' => $details,
                'hash' => EnrollmentService::hash($current->content, $details),
                'statement' => EnrollmentTexts::ACCEPTANCE_STATEMENT,
            ];
        } else {
            $agreement = ['accepted' => false, 'ready' => false];
        }

        // ---- Payment (bank details only AFTER the agreement is accepted)
        $status = $enrollment->payment_status;
        $method = $enrollment->paymentMethod ?? $a->cohort?->paymentMethod;
        $payment = [
            'status' => $status->value,
            'label' => $status->getLabel(),
            'amount' => $acceptance && $enrollment->amount !== null ? EnrollmentService::money($enrollment->amount, $enrollment->currency) : null,
            'deadline' => $acceptance ? ($acceptance->details_snapshot['payment_deadline'] ?? null) : null,
            'bank' => $acceptance && $method ? $method->studentDetails() : null,
            'can_upload' => $acceptance && ! $final && $status->acceptsReceipt(),
            'reupload_reason' => $status === PaymentStatus::ReuploadRequested ? $enrollment->reupload_reason : null,
            'receipts' => $enrollment->receipts->map(fn (PaymentReceipt $r) => [
                'id' => $r->uuid,
                'name' => $r->original_name,
                'uploaded_at' => self::when($r->created_at),
                'review' => PaymentReceipt::REVIEW[$r->review_status] ?? $r->review_status,
            ])->values()->all(),
            'max_mb' => EnrollmentTexts::RECEIPT_MAX_KB / 1024,
        ];

        $media = $a->latestMediaConsent;

        return [
            'ready' => ! $missing,
            'agreement' => $agreement,
            'media' => [
                'decided' => (bool) $media,
                'granted' => (bool) $media?->granted,
                'decided_at' => $media ? self::when($media->decided_at) : null,
                'text' => EnrollmentTexts::MEDIA_CONSENT_TEXT,
            ],
            'payment' => $payment,
            'seat' => [
                'confirmed' => (bool) $enrollment->seat_confirmed_at,
                'confirmed_at' => $enrollment->seat_confirmed_at ? self::when($enrollment->seat_confirmed_at) : null,
            ],
        ];
    }

    private static function when($date): ?string
    {
        return $date?->locale('ar')->translatedFormat('j F Y — g:i A');
    }
}
