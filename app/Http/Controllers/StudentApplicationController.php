<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\RegistrationStatus;
use App\Models\Cohort;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;
use App\Support\StudentApplicationForm as Q;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Receives the real student application (the approved 3-step form).
 * Answers with JSON so the approved form can show field errors in place
 * and, on success, the approved confirmation screen with the reference.
 */
class StudentApplicationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Spam trap (hidden field in the approved form). Pretend success, store nothing.
        if (filled($request->input('fl_website'))) {
            return response()->json(['reference' => 'FL-' . strtoupper(Str::random(8))], 201);
        }

        $cohort = Cohort::find($request->input('cohort_id'));
        if (! $cohort || ! $cohort->registration_status->acceptsApplications()) {
            return response()->json([
                'message' => 'التسجيل على هذه الدفعة مغلق حاليًا.',
                'code' => 'registration_closed',
            ], 409);
        }

        // Normalise before validating
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'phone' => preg_replace('/[\s\-()]/', '', (string) $request->input('phone')),
            'gaps'  => array_values(array_filter((array) $request->input('gaps', []))),
        ]);

        $maxYear = (int) now()->format('Y') + 8;

        $validator = Validator::make($request->all(), [
            'full_name'          => ['required', 'string', 'min:3', 'max:120'],
            'email'              => ['required', 'email', 'max:190',
                Rule::unique('student_applications', 'email')->where('cohort_id', $cohort->id)],
            'phone'              => ['required', 'regex:/^\+?\d{8,15}$/'],
            'gender'             => ['required', Rule::in(array_keys(Q::GENDER))],
            'city'               => ['required', 'string', 'max:100'],
            'university'         => ['required', 'string', 'max:150'],
            'major'              => ['required', 'string', 'max:150'],
            'study_status'       => ['required', Rule::in(array_keys(Q::STUDY_STATUS))],
            'graduation_year'    => ['required', 'integer', 'min:1980', 'max:' . $maxYear],

            'motivation'         => ['required', 'string', 'max:1500'],
            'gaps'               => ['required', 'array', 'min:1'],
            'gaps.*'             => [Rule::in(array_keys(Q::GAPS))],
            'gaps_other'         => ['nullable', 'string', 'max:150'],
            'has_experience'     => ['required', Rule::in(['yes', 'no'])],
            'experience_details' => ['nullable', 'required_if:has_experience,yes', 'string', 'max:1500'],
            'team_scenario'      => ['required', 'string', 'max:1500'],
            'weekly_commitment'  => ['required', Rule::in(array_keys(Q::WEEKLY_COMMITMENT))],

            'linkedin_url'       => ['nullable', 'url:http,https', 'max:500'],
            'portfolio_url'      => ['nullable', 'url:http,https', 'max:500'],
            'cv'                 => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:' . Q::CV_MAX_KB],
            'consent'            => ['accepted'],
        ], $this->messages($maxYear));

        // "أخرى" selected → its text becomes required
        $validator->sometimes('gaps_other', 'required', fn ($input) => in_array('other', (array) $input->gaps, true));

        if ($validator->fails()) {
            return response()->json([
                'message' => 'تحقّق من الحقول المطلوبة.',
                'errors' => collect($validator->errors()->toArray())
                    ->mapWithKeys(fn ($msgs, $key) => [Str::before($key, '.') => $msgs[0]]),
            ], 422);
        }

        $data = $validator->validated();
        $file = $request->file('cv');

        // Private storage, random name — the original name is kept only as metadata.
        $cvPath = $file->storeAs(
            now()->format('Y/m'),
            Str::random(40) . '.pdf',
            StudentApplication::CV_DISK
        );

        try {
            $application = DB::transaction(function () use ($data, $cohort, $file, $cvPath) {
                $student = Student::updateOrCreate(
                    ['email' => $data['email']],
                    ['full_name' => $data['full_name'], 'phone' => $data['phone']]
                );

                // Every new application starts as «تم الاستلام». If the cohort was in
                // waitlist mode it is flagged so the team can see and filter it.
                $waitlist = $cohort->registration_status === RegistrationStatus::Waitlist;
                $status = ApplicationStatus::Received;

                $application = StudentApplication::create([
                    'reference'              => $this->newReference(),
                    'student_id'             => $student->id,
                    'cohort_id'              => $cohort->id,
                    'status'                 => $status,
                    'status_changed_at'      => now(),
                    'submitted_via_waitlist' => $waitlist,
                    'full_name'              => $data['full_name'],
                    'email'                  => $data['email'],
                    'phone'                  => $data['phone'],
                    'gender'                 => $data['gender'],
                    'city'                   => $data['city'],
                    'university'             => $data['university'],
                    'major'                  => $data['major'],
                    'study_status'           => $data['study_status'],
                    'graduation_year'        => $data['graduation_year'],
                    'motivation'             => $data['motivation'],
                    'gaps'                   => array_values($data['gaps']),
                    'gaps_other'             => in_array('other', $data['gaps'], true) ? ($data['gaps_other'] ?? null) : null,
                    'has_experience'         => $data['has_experience'] === 'yes',
                    'experience_details'     => $data['has_experience'] === 'yes' ? ($data['experience_details'] ?? null) : null,
                    'team_scenario'          => $data['team_scenario'],
                    'weekly_commitment'      => $data['weekly_commitment'],
                    'linkedin_url'           => $data['linkedin_url'] ?? null,
                    'portfolio_url'          => $data['portfolio_url'] ?? null,
                    'cv_path'                => $cvPath,
                    'cv_original_name'       => Str::limit($file->getClientOriginalName(), 240, ''),
                    'cv_size'                => $file->getSize(),
                    'consented_at'           => now(),
                ]);

                $application->statusChanges()->create([
                    'from_status' => null,
                    'to_status'   => $status->value,
                    'changed_by'  => null,
                ]);

                return $application;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Two submissions at the same moment with the same e-mail.
            \Illuminate\Support\Facades\Storage::disk(StudentApplication::CV_DISK)->delete($cvPath);
            return response()->json([
                'message' => 'تحقّق من الحقول المطلوبة.',
                'errors' => ['email' => 'يوجد طلب مسجّل بهذا البريد في هذه الدفعة.'],
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Storage::disk(StudentApplication::CV_DISK)->delete($cvPath);
            throw $e;
        }

        $this->notifyTeam($application);

        return response()->json(['reference' => $application->reference], 201);
    }

    private function newReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no look-alike characters
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $reference = 'FL-' . now()->format('y') . '-' . $code;
        } while (StudentApplication::where('reference', $reference)->exists());

        return $reference;
    }

    /** Filament bell notification for the team — same style as the existing contact form. */
    private function notifyTeam(StudentApplication $application): void
    {
        try {
            $notification = Notification::make()
                ->title('طلب تسجيل جديد في المحاكاة')
                ->body($application->full_name . ' — ' . $application->reference)
                ->icon('heroicon-o-academic-cap')
                ->success()
                ->actions([
                    Action::make('open')
                        ->label('فتح الطلب')
                        ->button()
                        ->url(\App\Filament\Resources\StudentApplicationResource::getUrl('view', ['record' => $application])),
                    Action::make('markAsRead')->label('وضع علامة مقروء')->markAsRead(),
                ]);

            // Delivered immediately (no background worker needed).
            User::where('is_active', 1)->get()->each(
                fn (User $user) => $user->notifyNow($notification->toDatabase())
            );
        } catch (\Throwable $e) {
            Log::warning('Fluent: team notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function messages(int $maxYear): array
    {
        return [
            'required'                    => 'هذا الحقل مطلوب.',
            'full_name.min'               => 'اكتب اسمك الثلاثي.',
            'max'                         => 'النص أطول من المسموح.',
            'email.email'                 => 'تأكد من صيغة البريد الإلكتروني.',
            'email.unique'                => 'يوجد طلب مسجّل بهذا البريد في هذه الدفعة.',
            'phone.regex'                 => 'أدخل رقم جوال صحيح (مثال: 0512345678).',
            'in'                          => 'اختر أحد الخيارات.',
            'gaps.required'               => 'اختر خيارًا واحدًا على الأقل.',
            'gaps.min'                    => 'اختر خيارًا واحدًا على الأقل.',
            'gaps.*.in'                   => 'اختر من الخيارات المتاحة.',
            'gaps_other.required'         => 'اكتب ما تقصده بـ «أخرى».',
            'experience_details.required_if' => 'احكِ لنا باختصار عن التجربة.',
            'graduation_year.integer'     => 'أدخل سنة صحيحة.',
            'graduation_year.min'         => 'أدخل سنة صحيحة.',
            'graduation_year.max'         => 'أعلى سنة مسموحة ' . $maxYear . '.',
            'url'                         => 'أدخل رابطًا صحيحًا يبدأ بـ https://',
            'cv.required'                 => 'الرجاء إرفاق السيرة الذاتية.',
            'cv.file'                     => 'تعذّر رفع الملف. أعد المحاولة.',
            'cv.mimes'                    => 'الملف يجب أن يكون بصيغة PDF.',
            'cv.mimetypes'                => 'الملف يجب أن يكون بصيغة PDF.',
            'cv.max'                      => 'حجم الملف أكبر من الحد المسموح (5 ميجابايت).',
            'consent.accepted'            => 'لا بد من الموافقة للمتابعة.',
        ];
    }
}
