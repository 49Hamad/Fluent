{{--
    «مساحتي في Fluent» — approved prototype UI (portal.html), LIVE (Phase 2).
    Only for a signed-in student (EnsureStudentIsAuthenticated). The data comes
    from StudentPortalController@data, built by App\Support\StudentPortalData
    (strict allow-list: no internal notes, no CV path, no history, no Filament data).
    Seat-confirmation steps (agreement → media consent → bank transfer → receipt)
    post to StudentEnrollmentController; data from App\Support\StudentEnrollmentData.
--}}
<x-layouts.fluent :title="'مساحتي في Fluent'" :noindex="true" :chrome="false" :site-script="false" :app-css="true">
@push('head')
  <link rel="stylesheet" href="{{ asset('fluent/css/fluent-enroll.css') }}?v={{ @filemtime(public_path('fluent/css/fluent-enroll.css')) }}">
@endpush
<div class="app">
  <div class="demobar" id="demobar" hidden></div>

  <header class="appbar">
    <div class="wrap appbar-in">
      <a class="logo" href="{{ route('home') }}" aria-label="فلوانت Fluent">
        <span class="logo-mark"><svg viewBox="0 0 990 500" aria-hidden="true"><use href="#fl-mark"/></svg></span>
        <span class="logo-word">Fluent</span>
      </a>
      <span class="app-tag">مساحتي</span>
      <nav class="app-nav" id="pnav" aria-label="أقسام مساحتي">
        <button type="button" data-view="overview" class="is-on">طلبي</button>
        <button type="button" data-view="profile">الملف الشخصي</button>
      </nav>
      <div class="app-user">
        <span class="app-who" id="who"></span>
        <button class="app-out" type="button" id="signout">خروج</button>
      </div>
    </div>
  </header>

  <main class="app-main">
    <div class="wrap" id="pview"></div>
  </main>
</div>

@push('scripts')
  <script>
    window.FLUENT_CONFIG = { previewMode: false, contactEmail: @json(\App\Support\FluentContact::email()) };
    window.FLUENT_PORTAL = {
      dataUrl: @json(route('fluent.portal.data')),
      logoutUrl: @json(route('fluent.logout')),
      loginUrl: @json(route('fluent.login')),
      csrf: @json(csrf_token()),
      agreementUrl: @json(route('fluent.portal.agreement')),
      mediaUrl: @json(route('fluent.portal.media')),
      receiptUrl: @json(route('fluent.portal.receipt')),
      receiptDownloadUrl: @json(url('/portal/receipts')),
      application: @json(request()->query('application')),
      studentName: @json(auth('student')->user()?->full_name)
    };
  </script>
  <script src="{{ asset('fluent/js/fluent-portal-data.js') }}?v={{ @filemtime(public_path('fluent/js/fluent-portal-data.js')) }}"></script>
  <script src="{{ asset('fluent/js/fluent-portal.js') }}?v={{ @filemtime(public_path('fluent/js/fluent-portal.js')) }}"></script>
@endpush
</x-layouts.fluent>
