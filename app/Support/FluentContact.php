<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Fluent's PUBLIC contact details (shown to visitors on the redesigned site).
 *
 * Source of truth: Filament → "الإعدادات العامة" (Settings) → tab
 * "روابط التواصل الاجتماعي" → the first repeater (Address), rows whose
 * type is "Email" / "Phone" / "Address". Editing them there updates
 * the whole public site — no code change needed.
 *
 * config('fluent.contact') is only a fallback used when a field is left
 * empty in Filament, so the site never shows a blank contact line.
 *
 * NOT used for internal notifications: the contact form still e-mails
 * the address configured in ShowContactUslPage (unchanged).
 */
class FluentContact
{
    private static ?array $rows = null;

    private static function value(string $type): ?string
    {
        if (self::$rows === null) {
            self::$rows = collect(Setting::first()?->Address ?? [])
                ->filter(fn ($row) => filled($row['name'] ?? null))
                ->mapWithKeys(fn ($row) => [$row['social_type'] => trim($row['name'])])
                ->all();
        }

        return self::$rows[$type] ?? null;
    }

    public static function email(): ?string
    {
        return self::value('email') ?: config('fluent.contact.email');
    }

    public static function phone(): ?string
    {
        return self::value('phone') ?: config('fluent.contact.phone');
    }

    public static function address(): ?string
    {
        return self::value('address');
    }

    /** tel: link target, digits and leading + only. */
    public static function phoneHref(): ?string
    {
        $phone = self::phone();

        return $phone ? 'tel:' . preg_replace('/[^\d+]/', '', $phone) : null;
    }
}
