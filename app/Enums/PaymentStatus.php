<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Payment progress of a seat-confirmation track — SEPARATE from the
 * application status (ApplicationStatus). Verifying a payment never
 * final-accepts an application by itself.
 */
enum PaymentStatus: string implements HasLabel, HasColor
{
    case AwaitingTransfer  = 'awaiting_transfer';
    case ReceiptUploaded   = 'receipt_uploaded';
    case UnderReview       = 'under_review';
    case Verified          = 'verified';
    case ReuploadRequested = 'reupload_requested';

    public function getLabel(): string
    {
        return match ($this) {
            self::AwaitingTransfer  => 'بانتظار التحويل',
            self::ReceiptUploaded   => 'تم رفع الإيصال',
            self::UnderReview       => 'قيد التحقق',
            self::Verified          => 'تم التحقق من السداد',
            self::ReuploadRequested => 'يحتاج إعادة رفع',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AwaitingTransfer  => 'gray',
            self::ReceiptUploaded   => 'warning',
            self::UnderReview       => 'info',
            self::Verified          => 'success',
            self::ReuploadRequested => 'danger',
        };
    }

    /** The student may upload a (new) receipt only in these states. */
    public function acceptsReceipt(): bool
    {
        return in_array($this, [self::AwaitingTransfer, self::ReuploadRequested], true);
    }
}
