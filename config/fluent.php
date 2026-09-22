<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend review mode (redesign)
    |--------------------------------------------------------------------------
    | The new student application, business challenge, student login and
    | student portal screens are migrated from the approved design BEFORE
    | their Laravel backend exists. While this is true:
    |   - the forms validate locally and show the confirmation screen,
    |     but send and store NOTHING (no database, no email, no storage)
    |   - the login screen never signs anyone in
    |   - the portal can be previewed with in-memory mock data at
    |     /preview/portal (this route only exists when this flag is true)
    |
    | Default: enabled everywhere EXCEPT production. Set
    | FLUENT_FRONTEND_PREVIEW=false to switch these screens off.
    */
    'frontend_preview' => (bool) env('FLUENT_FRONTEND_PREVIEW', env('APP_ENV', 'production') !== 'production'),


    /*
    |--------------------------------------------------------------------------
    | Public contact details — FALLBACK ONLY
    |--------------------------------------------------------------------------
    | The real values are edited in Filament → الإعدادات العامة → روابط التواصل
    | الاجتماعي (Email / Phone rows). These are used only if those are empty.
    */
    'contact' => [
        'email' => 'info@fluent.sa',
        'phone' => '+966547291315',
    ],

];
