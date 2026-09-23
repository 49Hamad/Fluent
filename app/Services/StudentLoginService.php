<?php

namespace App\Services;

use App\Mail\StudentLoginCodeMail;
use App\Models\Student;
use App\Models\StudentLoginCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Password-less student sign-in with a 6-digit e-mail code.
 *
 *  - A code is sent ONLY if the e-mail belongs to a student with an application,
 *    but the visitor always gets the same neutral answer (no account discovery).
 *  - Codes: 10 minutes, one use, max 5 wrong attempts, stored as a keyed hash.
 *  - Requesting a new code cancels the previous one.
 *  - Rate limits per e-mail (tight) and per IP (generous — students often share
 *    a university network) for both requesting and verifying. Limits are counted
 *    the same way whether or not the e-mail exists.
 */
class StudentLoginService
{
    public const RESEND_COOLDOWN_SECONDS = 60;

    /* Per 10 minutes. Per e-mail = one person; per IP = a whole shared network. */
    public const CODE_PER_EMAIL = 3;
    public const CODE_PER_IP = 60;
    public const VERIFY_FAILS_PER_EMAIL = 10;
    public const VERIFY_FAILS_PER_IP = 100;

    /**
     * @return array{ok: bool, retry_after?: int}
     */
    public function requestCode(string $email, string $ip): array
    {
        $email = Str::lower(trim($email));
        $emailKey = 'student-code-email:' . sha1($email);
        $ipKey = 'student-code-ip:' . $ip;

        // Max 3 codes / 10 min per e-mail, 60 / 10 min per IP (whether or not the e-mail exists).
        foreach ([[$emailKey, self::CODE_PER_EMAIL], [$ipKey, self::CODE_PER_IP]] as [$key, $max]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return ['ok' => false, 'retry_after' => RateLimiter::availableIn($key)];
            }
        }
        RateLimiter::hit($emailKey, 600);
        RateLimiter::hit($ipKey, 600);

        $student = Student::where('email', $email)->whereHas('applications')->first();
        if (! $student) {
            return ['ok' => true];   // neutral answer, nothing sent
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($student, $code) {
            // Cancel any previous unused code, and clean up old rows.
            $student->loginCodes()->whereNull('consumed_at')->update(['consumed_at' => now()]);
            $student->loginCodes()->where('created_at', '<', now()->subDay())->delete();

            $student->loginCodes()->create([
                'code_hash' => StudentLoginCode::hashCode($code),
                'expires_at' => now()->addMinutes(StudentLoginCode::TTL_MINUTES),
            ]);
        });

        // Sent after the response is returned, so response time does not reveal
        // whether the e-mail exists.
        dispatch(function () use ($student, $code) {
            try {
                Mail::to($student->email)->send(new StudentLoginCodeMail($student->full_name, $code));
            } catch (\Throwable $e) {
                Log::error('Fluent: student login code e-mail failed', ['student' => $student->id, 'error' => $e->getMessage()]);
            }
        })->afterResponse();

        return ['ok' => true];
    }

    /**
     * @return array{student: ?Student, retry_after?: int}
     */
    public function verify(string $email, string $code, string $ip): array
    {
        $email = Str::lower(trim($email));
        $code = preg_replace('/\D/', '', $code);
        $emailKey = 'student-verify-email:' . sha1($email);
        $ipKey = 'student-verify-ip:' . $ip;

        // Wrong codes: max 10 / 10 min per e-mail, 100 / 10 min per IP (plus 5 tries per code).
        foreach ([[$emailKey, self::VERIFY_FAILS_PER_EMAIL], [$ipKey, self::VERIFY_FAILS_PER_IP]] as [$key, $max]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return ['student' => null, 'retry_after' => RateLimiter::availableIn($key)];
            }
        }

        $student = Student::where('email', $email)->first();
        $login = $student?->loginCodes()
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $login || strlen($code) !== 6) {
            RateLimiter::hit($emailKey, 600);
            RateLimiter::hit($ipKey, 600);
            return ['student' => null];
        }

        if (! hash_equals($login->code_hash, StudentLoginCode::hashCode($code))) {
            RateLimiter::hit($emailKey, 600);
            RateLimiter::hit($ipKey, 600);
            $attempts = $login->attempts + 1;
            $login->update([
                'attempts' => $attempts,
                // Lock the code after too many wrong attempts.
                'consumed_at' => $attempts >= StudentLoginCode::MAX_ATTEMPTS ? now() : null,
            ]);
            return ['student' => null];
        }

        // One use only — atomic, so two simultaneous correct submissions cannot both succeed.
        $claimed = StudentLoginCode::whereKey($login->id)->whereNull('consumed_at')->update(['consumed_at' => now()]);
        if ($claimed !== 1) {
            return ['student' => null];
        }

        RateLimiter::clear($emailKey);

        return ['student' => $student];
    }
}
