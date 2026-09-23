{{--
    Student sign-in — approved prototype UI (login.html in fluent-site-updated.zip).
    LIVE (Phase 2): e-mail → 6-digit code sent by e-mail → «مساحتي في Fluent».
    See StudentAuthController / StudentLoginService. Separate "student" guard:
    not connected to employee / Filament accounts. No passwords, no localStorage.
--}}
<x-layouts.fluent :title="'تسجيل الدخول — Fluent'" :noindex="true" :chrome="false" :site-script="false" :app-css="true">
<main class="authwrap">
  <div class="authbg" aria-hidden="true"><div class="g"></div></div>

  <div class="authcard rise">
    <a class="logo" href="{{ route('home') }}" aria-label="فلوانت Fluent — الصفحة الرئيسية">
      <span class="logo-mark"><svg viewBox="0 0 990 500" aria-hidden="true"><use href="#fl-mark"/></svg></span>
      <span class="logo-word">Fluent</span>
    </a>

    <h1>تسجيل الدخول</h1>
    <p class="sub">أدخل بريدك الإلكتروني وسنرسل لك رمز دخول. لا حاجة لكلمة مرور.</p>

    <div id="auth-slot"></div>

    <p class="authfoot">
      ما عندك حساب بعد؟ <a href="{{ route('fluent.apply') }}">سجّل في المحاكاة</a><br>
      <span class="tiny">جهة أو شركة؟ <a href="{{ route('fluent.challenge') }}">شاركنا تحديًا</a></span>
    </p>
  </div>
</main>

@push('scripts')
  <script>
    window.FLUENT_LOGIN = {
      codeUrl: @json(route('fluent.login.code')),
      verifyUrl: @json(route('fluent.login.verify')),
      csrf: @json(csrf_token())
    };
  </script>
  <script src="{{ asset('fluent/js/fluent-login.js') }}?v={{ @filemtime(public_path('fluent/js/fluent-login.js')) }}"></script>
@endpush
</x-layouts.fluent>
