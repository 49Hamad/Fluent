<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Mail\StudentApplicationStatusMail;
use App\Models\StudentApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The ONE place where an application's status changes.
 * Used by Filament (single + bulk). Records history and, only when the
 * employee ticks "إبلاغ الطالب بالبريد", e-mails the student.
 */
class StudentApplicationStatusService
{
    /**
     * @return array{changed: bool, emailed: bool, email_failed: bool}
     */
    public function change(
        StudentApplication $application,
        ApplicationStatus $to,
        ?User $by,
        bool $notifyStudent = false,
        ?string $messageToStudent = null,
    ): array {
        $from = $application->status;
        $changed = $from !== $to;

        DB::transaction(function () use ($application, $from, $to, $by, $changed, $notifyStudent) {
            if ($changed) {
                $application->forceFill(['status' => $to, 'status_changed_at' => now()])->save();
            }
            $application->statusChanges()->create([
                'from_status' => $from?->value,
                'to_status' => $to->value,
                'changed_by' => $by?->id,
                'student_notified' => false,
            ]);
        });

        $emailed = false;
        $failed = false;

        if ($notifyStudent) {
            try {
                // Sent immediately (not queued) so the employee gets a real result.
                Mail::to($application->email)->send(
                    new StudentApplicationStatusMail($application->fresh(['cohort']), $messageToStudent)
                );
                $application->statusChanges()->first()?->update(['student_notified' => true]);
                $emailed = true;
            } catch (\Throwable $e) {
                Log::error('Fluent: status e-mail failed', ['application' => $application->reference, 'error' => $e->getMessage()]);
                $failed = true;
            }
        }

        return ['changed' => $changed, 'emailed' => $emailed, 'email_failed' => $failed];
    }
}
