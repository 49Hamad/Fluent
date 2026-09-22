<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Redesign — new public screens in FRONTEND REVIEW MODE.
 *
 * The approved UI (student application, business challenge, student login,
 * student portal) is shown before its Laravel backend exists:
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

    public function apply()
    {
        return $this->reviewView('fluent.pages.apply');
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
                'contactEmail' => $this->contactEmail(),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function reviewView(string $view)
    {
        abort_unless(config('fluent.frontend_preview'), 404);

        return view($view);
    }

    private function contactEmail(): string
    {
        return collect(Setting::first()?->Address ?? [])
            ->firstWhere('social_type', 'email')['name'] ?? 'fluent@fluent.sa';
    }
}
