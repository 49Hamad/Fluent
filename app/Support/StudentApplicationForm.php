<?php

namespace App\Support;

/**
 * The approved student application questions (Phase 2).
 *
 * Single place for the answer OPTIONS: the same keys are used by the public
 * form (public/fluent/js/fluent-schemas.js), server-side validation and the
 * readable labels shown in Filament. Changing a label here changes it in
 * Filament; changing a key requires updating fluent-schemas.js too.
 */
class StudentApplicationForm
{
    public const GENDER = [
        'male'   => 'ذكر',
        'female' => 'أنثى',
    ];

    public const STUDY_STATUS = [
        'student'  => 'طالب',
        'graduate' => 'خريج',
    ];

    public const GAPS = [
        'practical_experience'       => 'خبرة عملية',
        'teamwork'                   => 'العمل ضمن فريق',
        'professional_communication' => 'التواصل المهني',
        'real_tasks'                 => 'التعامل مع مهام حقيقية',
        'time_management'            => 'إدارة الوقت والالتزام',
        'workplace_confidence'       => 'الثقة في بيئة العمل',
        'portfolio'                  => 'بناء ملف أعمال',
        'other'                      => 'أخرى',
    ];

    public const WEEKLY_COMMITMENT = [
        'lt5'   => 'أقل من 5 ساعات',
        '5_10'  => '5–10 ساعات',
        '10_15' => '10–15 ساعة',
        'gt15'  => 'أكثر من 15 ساعة',
    ];

    public const CONSENT_TEXT = 'أوافق على استخدام بياناتي المقدمة لأغراض دراسة الطلب والتواصل معي بشأن برامج وتجارب Fluent.';

    public const CV_MAX_KB = 5120; // 5 MB, PDF only

    public static function label(array $options, ?string $key): ?string
    {
        return $key === null ? null : ($options[$key] ?? $key);
    }
}
