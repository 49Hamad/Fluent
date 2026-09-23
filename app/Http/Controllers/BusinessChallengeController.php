<?php

namespace App\Http\Controllers;

use App\Enums\ChallengeStatus;
use App\Filament\Resources\BusinessChallengeResource;
use App\Models\BusinessChallenge;
use App\Models\User;
use App\Support\BusinessChallengeForm as Q;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Receives the real «شاركنا تحديًا» form (approved questions, Phase 2).
 * Answers with JSON so the approved form shows field errors in place and,
 * on success, the approved confirmation screen with the reference number.
 */
class BusinessChallengeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Spam trap (hidden field in the approved form). Pretend success, store nothing.
        if (filled($request->input('fl_website'))) {
            return response()->json(['reference' => 'CH-' . strtoupper(Str::random(8))], 201);
        }

        // Normalise before validating
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'phone' => preg_replace('/[\s\-()]/', '', (string) $request->input('phone')),
            'expected_outputs' => array_values(array_filter((array) $request->input('expected_outputs', []))),
        ]);

        $validator = Validator::make($request->all(), [
            // Step 1 — عن الجهة
            'org_name'               => ['required', 'string', 'min:2', 'max:150'],
            'org_type'               => ['required', Rule::in(array_keys(Q::ORG_TYPE))],
            'org_type_other'         => ['nullable', 'required_if:org_type,other', 'string', 'max:150'],
            'sector'                 => ['required', 'string', 'max:150'],
            'contact_name'           => ['required', 'string', 'min:2', 'max:120'],
            'job_title'              => ['nullable', 'string', 'max:120'],
            'email'                  => ['required', 'email', 'max:190'],
            'phone'                  => ['required', 'regex:/^\+?\d{8,15}$/'],

            // Step 2 — عن التحدي
            'challenge_description'  => ['required', 'string', 'max:3000'],
            'affected_parties'       => ['required', 'string', 'max:1500'],
            'current_impact'         => ['required', 'string', 'max:1500'],
            'expected_outputs'       => ['required', 'array', 'min:1'],
            'expected_outputs.*'     => [Rule::in(array_keys(Q::EXPECTED_OUTPUTS))],
            'expected_outputs_other' => ['nullable', 'string', 'max:150'],
            'can_share_materials'    => ['required', Rule::in(['yes', 'no'])],
            'has_confidential_info'  => ['required', Rule::in(['yes', 'no'])],
            'confidential_details'   => ['nullable', 'required_if:has_confidential_info,yes', 'string', 'max:2000'],
            'challenge_file'         => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:' . Q::FILE_MAX_KB],
            'consent'                => ['accepted'],
        ], $this->messages());

        // "أخرى" selected → its text becomes required
        $validator->sometimes('expected_outputs_other', 'required',
            fn ($input) => in_array('other', (array) $input->expected_outputs, true));

        if ($validator->fails()) {
            return response()->json([
                'message' => 'تحقّق من الحقول المطلوبة.',
                'errors' => collect($validator->errors()->toArray())
                    ->mapWithKeys(fn ($msgs, $key) => [Str::before($key, '.') => $msgs[0]]),
            ], 422);
        }

        $data = $validator->validated();
        $file = $request->file('challenge_file');

        // Private storage, random name — the original name is kept only as metadata.
        $filePath = $file?->storeAs(now()->format('Y/m'), Str::random(40) . '.pdf', BusinessChallenge::FILE_DISK);

        try {
            $challenge = BusinessChallenge::create([
                'reference'              => $this->newReference(),
                'status'                 => ChallengeStatus::New,
                'status_changed_at'      => now(),
                'org_name'               => $data['org_name'],
                'org_type'               => $data['org_type'],
                'org_type_other'         => $data['org_type'] === 'other' ? ($data['org_type_other'] ?? null) : null,
                'sector'                 => $data['sector'],
                'contact_name'           => $data['contact_name'],
                'job_title'              => $data['job_title'] ?? null,
                'email'                  => $data['email'],
                'phone'                  => $data['phone'],
                'challenge_description'  => $data['challenge_description'],
                'affected_parties'       => $data['affected_parties'],
                'current_impact'         => $data['current_impact'],
                'expected_outputs'       => array_values($data['expected_outputs']),
                'expected_outputs_other' => in_array('other', $data['expected_outputs'], true) ? ($data['expected_outputs_other'] ?? null) : null,
                'can_share_materials'    => $data['can_share_materials'] === 'yes',
                'has_confidential_info'  => $data['has_confidential_info'] === 'yes',
                'confidential_details'   => $data['has_confidential_info'] === 'yes' ? ($data['confidential_details'] ?? null) : null,
                'file_path'              => $filePath,
                'file_original_name'     => $file ? Str::limit($file->getClientOriginalName(), 240, '') : null,
                'file_size'              => $file?->getSize(),
                'consented_at'           => now(),
            ]);
        } catch (\Throwable $e) {
            if ($filePath) {
                Storage::disk(BusinessChallenge::FILE_DISK)->delete($filePath);
            }
            throw $e;
        }

        $this->notifyTeam($challenge);

        return response()->json(['reference' => $challenge->reference], 201);
    }

    private function newReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no look-alike characters
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $reference = 'CH-' . now()->format('y') . '-' . $code;
        } while (BusinessChallenge::where('reference', $reference)->exists());

        return $reference;
    }

    /** Filament bell notification — only employees allowed to see company challenges. */
    private function notifyTeam(BusinessChallenge $challenge): void
    {
        try {
            $notification = Notification::make()
                ->title('تحدٍّ جديد من جهة')
                ->body($challenge->org_name . ' — ' . $challenge->reference)
                ->icon('heroicon-o-light-bulb')
                ->success()
                ->actions([
                    Action::make('open')
                        ->label('فتح التحدي')
                        ->button()
                        ->url(BusinessChallengeResource::getUrl('view', ['record' => $challenge])),
                    Action::make('markAsRead')->label('وضع علامة مقروء')->markAsRead(),
                ]);

            // Delivered immediately (no background worker needed).
            User::where('is_active', 1)->get()
                ->filter(fn (User $user) => $user->can('view_any_business::challenge'))
                ->each(fn (User $user) => $user->notifyNow($notification->toDatabase()));
        } catch (\Throwable $e) {
            Log::warning('Fluent: challenge notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function messages(): array
    {
        return [
            'required'                        => 'هذا الحقل مطلوب.',
            'min'                             => 'النص أقصر من المطلوب.',
            'max'                             => 'النص أطول من المسموح.',
            'email.email'                     => 'تأكد من صيغة البريد الإلكتروني.',
            'phone.regex'                     => 'أدخل رقم جوال صحيح (مثال: 0512345678).',
            'in'                              => 'اختر أحد الخيارات.',
            'org_type_other.required_if'      => 'وضّح نوع الجهة.',
            'expected_outputs.required'       => 'اختر خيارًا واحدًا على الأقل.',
            'expected_outputs.min'            => 'اختر خيارًا واحدًا على الأقل.',
            'expected_outputs.*.in'           => 'اختر من الخيارات المتاحة.',
            'expected_outputs_other.required' => 'اكتب ما تقصده بـ «أخرى».',
            'confidential_details.required_if'=> 'وضّح لنا المعلومات السرية أو القيود.',
            'challenge_file.file'             => 'تعذّر رفع الملف. أعد المحاولة.',
            'challenge_file.uploaded'         => 'تعذّر رفع الملف. تأكد أن حجمه لا يتجاوز 5 ميجابايت.',
            'challenge_file.mimes'            => 'الملف يجب أن يكون بصيغة PDF.',
            'challenge_file.mimetypes'        => 'الملف يجب أن يكون بصيغة PDF.',
            'challenge_file.max'              => 'حجم الملف أكبر من الحد المسموح (5 ميجابايت).',
            'consent.accepted'                => 'لا بد من الموافقة للمتابعة.',
        ];
    }
}
