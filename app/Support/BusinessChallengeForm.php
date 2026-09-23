<?php

namespace App\Support;

/**
 * The approved «شاركنا تحديًا» questions (Phase 2).
 *
 * Single place for the answer OPTIONS: the same keys are used by the public
 * form (public/fluent/js/fluent-schemas.js → challenge), server-side
 * validation and the readable labels in Filament.
 *
 * By design there is NO "what solution do you want?" question and NO
 * challenge title: the organisation describes the real problem; participants
 * work on solutions, and the Fluent team can name the challenge later.
 */
class BusinessChallengeForm
{
    public const ORG_TYPE = [
        'company'    => 'شركة',
        'government' => 'جهة حكومية',
        'nonprofit'  => 'جهة غير ربحية',
        'startup'    => 'مشروع ناشئ',
        'other'      => 'أخرى',
    ];

    public const EXPECTED_OUTPUTS = [
        'ideas'           => 'أفكار وحلول',
        'research'        => 'بحث وتحليل',
        'prototype'       => 'نموذج أولي',
        'experience'      => 'تحسين تجربة أو رحلة',
        'recommendations' => 'توصيات عملية',
        'other'           => 'أخرى',
    ];

    public const CONSENT_TEXT = 'أوافق على استخدام المعلومات المقدمة لغرض دراسة التحدي وتقييم ملاءمته للمحاكاة المهنية والتواصل معي بشأنه.';

    public const FILE_MAX_KB = 5120; // 5 MB, PDF only

    public static function label(array $options, ?string $key): ?string
    {
        return $key === null ? null : ($options[$key] ?? $key);
    }
}
