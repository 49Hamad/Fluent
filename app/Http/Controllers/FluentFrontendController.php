<?php

namespace App\Http\Controllers;

use App\Models\Cohort;

/**
 * Redesign — new public screens (all LIVE since Phase 2).
 *
 *  - Student application page → submissions: StudentApplicationController,
 *    driven by the cohort that is open / waitlist in Filament → الدفعات.
 *  - «شاركنا تحديًا» page      → submissions: BusinessChallengeController,
 *    managed in Filament → تحديات الشركات.
 *  - Student sign-in + portal → StudentAuthController / StudentPortalController.
 */
class FluentFrontendController extends Controller
{
    /** Real application page — state comes from Filament → الدفعات. */
    public function apply()
    {
        $cohort = Cohort::currentForApplications();

        return view('fluent.pages.apply', [
            'cohort' => $cohort,
            'registration' => $cohort?->registration_status->value ?? 'closed',
        ]);
    }

    /** Real «شاركنا تحديًا» page. */
    public function challenge()
    {
        return view('fluent.pages.challenge');
    }
}
