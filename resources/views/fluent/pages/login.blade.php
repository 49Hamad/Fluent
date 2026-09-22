{{--
    Student login — approved prototype UI (login.html in fluent-site-updated.zip).
    Markup copied from the prototype; page links are Laravel routes.

    FRONTEND REVIEW MODE: no authentication. The prototype's demo login
    (any code accepted, session in localStorage) is NOT migrated, and this
    screen is not connected to employee / Filament accounts. Trying to sign
    in shows a subtle development-only notice (see fluent-login.js).
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
  @include('fluent.partials.preview-config')
  <script src="{{ asset('fluent/js/fluent-login.js') }}"></script>
@endpush
</x-layouts.fluent>
