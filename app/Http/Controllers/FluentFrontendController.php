<?php

namespace App\Http\Controllers;

use App\Models\Cohort;

/**
 * Redesign — new public screens.
 *
 * LIVE (Phase 2): the student application page (real submission, see
 * StudentApplicationController), driven by the cohort whose registration
 * is open / waitlist in Filament → الدفعات.
 *
 * LIVE: student sign-in + portal → StudentAuthController / StudentPortalController.
 *
 * Still FRONTEND REVIEW MODE: the business challenge page only — it
 * validates locally and shows the confirmation screen, but sends / uploads /
 * stores NOTHING. It answers 404 when config('fluent.frontend_preview') is
 * false (the default in production). Its real workflow comes in a later phase.
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

    public function challenge()
    {
        return $this->reviewView('fluent.pages.challenge');
    }

    private function reviewView(string $view)
    {
        abort_unless(config('fluent.frontend_preview'), 404);

        return view($view);
    }
}
