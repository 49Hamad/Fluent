<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Support\FluentContact;
use Illuminate\Http\Request;

/**
 * Redesign — new public screens.
 *
 * LIVE (Phase 2): the student application page (real submission, see
 * StudentApplicationController), driven by the cohort whose registration
 * is open / waitlist in Filament → الدفعات.
 *
 * Still FRONTEND REVIEW MODE (business challenge, student login, portal):
 *   - forms validate locally and show the confirmation screen, but
 *     send / upload / store NOTHING
 *   - the login screen signs nobody in (no fake auth, not Filament auth)
 *   - the portal is only viewable through the development preview route,
 *     with in-memory mock data
 *
 * Everything here answers 404 when config('fluent.frontend_preview') is
 * false (the default in production). Real workflows replace these actions
 * in a later phase.
 */
class FluentFrontendController extends Controller
{
    private const PORTAL_STATES = ['received', 'review', 'interview', 'accepted', 'waitlist', 'rejected'];

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

    public function login()
    {
        return $this->reviewView('fluent.pages.login');
    }

    /** Real portal needs a signed-in student; without auth yet → login screen (as in the prototype). */
    public function portal()
    {
        return redirect()->route('fluent.login');
    }

    /** DEVELOPMENT ONLY: portal UI with in-memory mock data. */
    public function portalPreview(Request $request)
    {
        abort_unless(config('fluent.frontend_preview'), 404);

        $state = in_array($request->query('state'), self::PORTAL_STATES, true)
            ? $request->query('state')
            : 'review';

        return response()
            ->view('fluent.pages.portal-preview', [
                'state'        => $state,
                'contactEmail' => FluentContact::email(),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function reviewView(string $view)
    {
        abort_unless(config('fluent.frontend_preview'), 404);

        return view($view);
    }
}
