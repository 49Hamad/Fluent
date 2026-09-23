<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentApplication;
use App\Support\StudentPortalData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "مساحتي في Fluent" — the signed-in student's own applications only.
 * Every lookup goes through the signed-in student's relationship, so another
 * student's application can never be opened by changing the URL.
 */
class StudentPortalController extends Controller
{
    public function show(Request $request)
    {
        $this->selected($request);   // 404 early if a foreign reference is requested

        return view('fluent.pages.portal');
    }

    public function data(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        return response()->json(StudentPortalData::for($student, $this->selected($request)));
    }

    /**
     * One application → open it. Several (future) → the one asked for with
     * ?application=REFERENCE, otherwise the most recent.
     */
    private function selected(Request $request): ?StudentApplication
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();
        $ref = $request->query('application');

        if ($ref !== null) {
            return $student->applications()->where('reference', (string) $ref)->firstOrFail();
        }

        return $student->applications()->first();
    }
}
