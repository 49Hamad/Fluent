<?php

namespace App\Http\Controllers;

use App\Services\StudentLoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Student sign-in for "مساحتي في Fluent" — e-mail + one-time 6-digit code.
 * Uses the separate "student" guard; employees / Filament are not involved.
 */
class StudentAuthController extends Controller
{
    public function __construct(private StudentLoginService $login)
    {
    }

    public function show()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('fluent.portal');
        }

        return response()->view('fluent.pages.login')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function requestCode(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['email' => ['required', 'email', 'max:190']], [
            'email.required' => 'الرجاء إدخال البريد الإلكتروني.',
            'email.email' => 'تأكد من صيغة البريد الإلكتروني.',
            'email.max' => 'تأكد من صيغة البريد الإلكتروني.',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => ['email' => $v->errors()->first('email')]], 422);
        }

        $result = $this->login->requestCode($request->input('email'), $request->ip());
        if (! $result['ok']) {
            return response()->json([
                'message' => 'طلبات كثيرة لرمز الدخول. حاول بعد ' . max(1, (int) ceil($result['retry_after'] / 60)) . ' دقيقة.',
            ], 429);
        }

        // Same answer whether or not the e-mail has an application.
        return response()->json([
            'message' => 'إذا كان هذا البريد مرتبطًا بطلب في Fluent، ستصلك رسالة فيها رمز الدخول خلال دقائق.',
            'resend_after' => StudentLoginService::RESEND_COOLDOWN_SECONDS,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $result = $this->login->verify(
            (string) $request->input('email'),
            (string) $request->input('code'),
            $request->ip()
        );

        if (isset($result['retry_after'])) {
            return response()->json([
                'message' => 'محاولات كثيرة. حاول بعد ' . max(1, (int) ceil($result['retry_after'] / 60)) . ' دقيقة.',
            ], 429);
        }

        if (! $result['student']) {
            return response()->json([
                'errors' => ['code' => 'الرمز غير صحيح أو انتهت صلاحيته. تأكد من آخر رمز وصلك، أو اطلب رمزًا جديدًا.'],
            ], 422);
        }

        Auth::guard('student')->login($result['student']);
        $request->session()->regenerate();   // new session id after sign-in

        return response()->json(['redirect' => route('fluent.portal')]);
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->regenerateToken();

        return $request->expectsJson()
            ? response()->json(['redirect' => route('home')])
            : redirect()->route('home');
    }
}
