<?php

namespace App\Http\Controllers;

use App\Models\PaymentReceipt;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Services\EnrollmentService;
use App\Services\WorkflowException;
use App\Support\EnrollmentTexts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Seat-confirmation steps done by the signed-in student inside «مساحتي في Fluent».
 * Every request names the application by its reference and is resolved through
 * the signed-in student's own applications → another student's application
 * (or receipt) can never be reached. All rules are enforced in EnrollmentService.
 */
class StudentEnrollmentController extends Controller
{
    public function __construct(private EnrollmentService $service)
    {
    }

    public function acceptAgreement(Request $request): JsonResponse
    {
        $application = $this->application($request);
        $v = Validator::make($request->all(), [
            'agreement_id' => ['required', 'integer'],
            'hash' => ['required', 'string', 'size:64'],
            'confirm' => ['accepted'],
        ], ['confirm.accepted' => 'لا بد من الإقرار بقراءة الاتفاقية والموافقة عليها للمتابعة.']);

        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        return $this->run(fn () => $this->service->acceptAgreement(
            $application, (int) $request->input('agreement_id'), (string) $request->input('hash'),
            $request->ip(), $request->userAgent()
        ), 'تم حفظ موافقتك على الاتفاقية.');
    }

    public function mediaConsent(Request $request): JsonResponse
    {
        $application = $this->application($request);
        $v = Validator::make($request->all(), ['granted' => ['required', 'in:yes,no']]);
        if ($v->fails()) {
            return response()->json(['message' => 'اختر أحد الخيارين.'], 422);
        }

        return $this->run(fn () => $this->service->setMediaConsent(
            $application, $request->input('granted') === 'yes', $request->ip(), $request->userAgent()
        ), 'تم حفظ اختيارك.');
    }

    public function uploadReceipt(Request $request): JsonResponse
    {
        $application = $this->application($request);
        $v = Validator::make($request->all(), [
            'receipt' => ['required', 'file', 'extensions:pdf,jpg,jpeg,png', 'mimes:pdf,jpg,jpeg,png',
                'mimetypes:' . implode(',', array_keys(EnrollmentTexts::RECEIPT_MIMES)),
                'max:' . EnrollmentTexts::RECEIPT_MAX_KB],
        ], [
            'receipt.required' => 'اختر ملف الإيصال.',
            'receipt.uploaded' => 'تعذّر رفع الملف. تأكد أن حجمه لا يتجاوز 5 ميجابايت.',
            'receipt.file' => 'تعذّر رفع الملف. أعد المحاولة.',
            'receipt.extensions' => 'الصيغ المقبولة: PDF أو JPG أو PNG.',
            'receipt.mimes' => 'الصيغ المقبولة: PDF أو JPG أو PNG.',
            'receipt.mimetypes' => 'الصيغ المقبولة: PDF أو JPG أو PNG.',
            'receipt.max' => 'حجم الملف أكبر من الحد المسموح (5 ميجابايت).',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        return $this->run(fn () => $this->service->uploadReceipt($application, $request->file('receipt')),
            'تم رفع الإيصال. سيتحقق فريق Fluent من التحويل ويحدّث حالتك هنا.');
    }

    /** The owning student can download their own receipt (never a public URL). */
    public function downloadReceipt(Request $request, string $uuid): StreamedResponse
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();
        $receipt = PaymentReceipt::where('uuid', $uuid)
            ->whereHas('enrollment.application', fn ($q) => $q->where('student_id', $student->id))
            ->firstOrFail();

        abort_unless(Storage::disk(PaymentReceipt::DISK)->exists($receipt->file_path), 404);

        return Storage::disk(PaymentReceipt::DISK)->download($receipt->file_path, $receipt->original_name, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function application(Request $request): StudentApplication
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        return $student->applications()->with('cohort.paymentMethod')
            ->where('reference', (string) $request->input('application'))->firstOrFail();
    }

    private function run(callable $fn, string $ok): JsonResponse
    {
        try {
            $fn();
        } catch (WorkflowException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => $e->reason], 409);
        }

        return response()->json(['message' => $ok]);
    }
}
