<?php

namespace App\Support;

/**
 * Fixed texts of the seat-confirmation workflow. Stored with each decision
 * (so a later wording change never alters what a student agreed to).
 * Change CONSENT version when the media consent wording changes.
 */
class EnrollmentTexts
{
    public const ACCEPTANCE_STATEMENT = 'أقر بأنني قرأت اتفاقية المشاركة في برنامج Fluent واطلعت على بيانات الدفعة والرسوم وسياسة الانسحاب والاسترداد وأوافق عليها.';

    public const MEDIA_CONSENT_TEXT = 'أوافق على تصويري واستخدام ونشر صوري ومقاطع الفيديو التي أظهر فيها لأغراض توثيق والتعريف والتسويق لأنشطة وبرامج Fluent عبر موقعها الإلكتروني وحساباتها ومنصاتها الإعلامية.';

    public const MEDIA_CONSENT_VERSION = 'media-v1';

    public const RECEIPT_MAX_KB = 5120;   // 5 MB — PDF, JPG/JPEG, PNG

    public const RECEIPT_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
    ];
}
