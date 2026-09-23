<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Models\Student;
use App\Models\StudentApplication;

/**
 * Builds the ONLY data a student's browser ever receives for "مساحتي في Fluent".
 * An explicit allow-list: internal notes, CV file path, status history,
 * employee names and anything from Filament are never included.
 */
class StudentPortalData
{
    public static function for(Student $student, ?StudentApplication $selected): array
    {
        $applications = $student->applications()->with('cohort')->get();

        return [
            'student' => [
                'name' => $student->full_name,
            ],
            // Summary of all the student's applications (for a future "my experiences" list).
            'applications' => $applications->map(fn (StudentApplication $a) => [
                'reference' => $a->reference,
                'cohort' => $a->cohort?->name,
                'status' => $a->status->value,
                'created_at' => $a->created_at?->toIso8601String(),
            ])->values()->all(),
            'application' => $selected ? self::application($selected) : null,
        ];
    }

    public static function application(StudentApplication $a): array
    {
        $a->loadMissing('cohort');
        $status = $a->status;

        return [
            'id' => $a->reference,               // the portal uses the reference, never the database id
            'reference' => $a->reference,
            'status' => $status->value,
            'created_at' => $a->created_at?->toIso8601String(),
            'full_name' => $a->full_name,
            'email' => $a->email,
            'phone' => $a->phone,
            'data' => [
                'city' => $a->city,
                'university' => $a->university,
                'major' => $a->major,
                'cv' => ['file_name' => $a->cv_original_name],   // name only — no link, no path
            ],
            'cohort_name' => $a->cohort?->name,
            // Interview details — only while the student is invited to an interview.
            'interview' => $status === ApplicationStatus::Interview ? [
                // Formatted on the server exactly as entered in Filament (no browser time-zone shift).
                'at' => $a->interview_at?->locale('ar')->translatedFormat('l j F Y — g:i A'),
                'mode' => $a->interview_mode ? (StudentApplication::INTERVIEW_MODES[$a->interview_mode] ?? null) : null,
                'location' => $a->interview_location,
                'note' => $a->interview_note,
            ] : null,
            // Seat-confirmation workflow — only for «مقبول مبدئيًا» / «مقبول نهائيًا».
            'enrollment' => \App\Services\EnrollmentService::visibleFor($a) ? StudentEnrollmentData::for($a) : null,
            // Cohort details — only after FINAL acceptance.
            'cohort' => $status === ApplicationStatus::FinalAccepted && $a->cohort ? [
                'name' => $a->cohort->name,
                'startDate' => $a->cohort->start_date?->locale('ar')->translatedFormat('l j F Y'),
                'time' => $a->cohort->schedule,
                'location' => $a->cohort->location,
                'instructions' => $a->cohort->instructions,
                'bring' => $a->cohort->what_to_bring,
            ] : null,
        ];
    }
}
