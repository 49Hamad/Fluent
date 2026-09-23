<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public contact details — FALLBACK ONLY
    |--------------------------------------------------------------------------
    | The real values are edited in Filament → الإعدادات العامة → روابط التواصل
    | الاجتماعي (Email / Phone rows). These are used only if those are empty.
    */
    /*
    |--------------------------------------------------------------------------
    | Development / staging e-mail safety net
    |--------------------------------------------------------------------------
    | Outside production, if this is set, EVERY e-mail the app sends (student
    | login codes, status e-mails, …) goes to this address instead of the real
    | recipient. In local development MAIL_MAILER=log already writes e-mails to
    | storage/logs instead of sending them. Leave empty in production.
    */
    'dev_mail_to' => env('FLUENT_DEV_MAIL_TO'),

    'contact' => [
        'email' => 'info@fluent.sa',
        'phone' => '+966547291315',
    ],

];
