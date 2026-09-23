<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Student application status.
 *
 * Workflow: received → review → interview → preliminary_accepted → final_accepted
 * (waitlist / rejected can be set at any point).
 *
 * "preliminary_accepted" is deliberately its own state: later steps such as an
 * agreement, payment or seat confirmation can be added BETWEEN preliminary and
 * final acceptance (as extra records linked to the application) without
 * changing this list or the rest of the workflow. None of them exist yet.
 */
enum ApplicationStatus: string implements HasLabel, HasColor
{
    case Received            = 'received';
    case Review              = 'review';
    case Interview           = 'interview';
    case PreliminaryAccepted = 'preliminary_accepted';
    case FinalAccepted       = 'final_accepted';
    case Waitlist            = 'waitlist';
    case Rejected            = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Received            => 'تم الاستلام',
            self::Review              => 'قيد المراجعة',
            self::Interview           => 'مرشح للمقابلة',
            self::PreliminaryAccepted => 'مقبول مبدئيًا',
            self::FinalAccepted       => 'مقبول نهائيًا',
            self::Waitlist            => 'قائمة الانتظار',
            self::Rejected            => 'غير مقبول',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Received            => 'gray',
            self::Review              => 'info',
            self::Interview           => 'warning',
            self::PreliminaryAccepted => 'primary',
            self::FinalAccepted       => 'success',
            self::Waitlist            => 'warning',
            self::Rejected            => 'danger',
        };
    }

    /** Short explanation used in the optional e-mail to the student. */
    public function studentMessage(): string
    {
        return match ($this) {
            self::Received            => 'وصلنا طلبك وهو الآن في قائمة المراجعة. سنخبرك بأي تحديث.',
            self::Review              => 'فريق Fluent يراجع طلبك الآن. المراجعة تستغرق عادة أيام عمل قليلة.',
            self::Interview           => 'تم ترشيحك لمقابلة قصيرة.',
            self::PreliminaryAccepted => 'يسعدنا إبلاغك بقبولك مبدئيًا في تجربة Fluent. سنتواصل معك بالخطوة التالية قبل تأكيد مقعدك.',
            self::FinalAccepted       => 'مبروك! تم قبولك نهائيًا في تجربة Fluent. هذه تفاصيل دفعتك.',
            self::Waitlist            => 'طلبك اجتاز المراجعة، لكن مقاعد هذه الدفعة اكتملت. إذا فُتح مقعد أو بدأت دفعة جديدة ستصلك رسالة قبل الإعلان العام.',
            self::Rejected            => 'اكتمل النظر في طلبك لهذه الدفعة، ولم نتمكن من ترشيحك للدفعة الحالية. هذا لا يعني أن ملفك ضعيف — المقاعد محدودة. نرحّب بطلبك في الدفعات القادمة.',
        };
    }
}
