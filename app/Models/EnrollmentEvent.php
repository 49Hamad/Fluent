<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentEvent extends Model
{
    public const UPDATED_AT = null;

    public const LABELS = [
        'enrollment_opened'   => 'فُتح مسار تأكيد المقعد',
        'agreement_accepted'  => 'وافق الطالب على الاتفاقية',
        'media_granted'       => 'وافق على الاستخدام الإعلامي',
        'media_declined'      => 'لم يوافق على الاستخدام الإعلامي',
        'receipt_uploaded'    => 'رفع الطالب إيصال التحويل',
        'under_review'        => 'بدأ التحقق من السداد',
        'payment_verified'    => 'تم التحقق من السداد',
        'reupload_requested'  => 'طُلب إعادة رفع الإيصال',
        'seat_confirmed'      => 'تم تأكيد المقعد (قبول نهائي)',
    ];

    protected $fillable = ['enrollment_id', 'event', 'actor', 'user_id', 'from_status', 'to_status', 'note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
