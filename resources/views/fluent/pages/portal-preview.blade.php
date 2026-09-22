{{--
    Student portal "مساحتي في Fluent" — approved prototype UI (portal.html).
    DEVELOPMENT PREVIEW ONLY: served at /preview/portal, which exists only
    when FLUENT_FRONTEND_PREVIEW is enabled. Data comes from an in-memory
    mock (public/fluent/preview/fluent-portal-mock.js): no authentication,
    no database, no browser storage, no real student data.
    ?state=received|review|interview|accepted|waitlist|rejected
--}}
<x-layouts.fluent :title="'مساحتي في Fluent — معاينة'" :noindex="true" :chrome="false" :site-script="false" :app-css="true">
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
  <script>window.FLUENT_CONFIG = { previewMode: true, contactEmail: @json($contactEmail) };</script>
  @include('fluent.partials.preview-config')
  <script src="{{ asset('fluent/preview/fluent-portal-mock.js') }}"></script>
  <script src="{{ asset('fluent/js/fluent-portal.js') }}"></script>
@endpush
</x-layouts.fluent>
