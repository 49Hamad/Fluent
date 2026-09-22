{{--
    TEMPORARY development page for /apply, /challenge and /portal.
    Header block uses the prototype's page head (.fhead). It clearly says the
    workflow is not live yet and collects NO data. It will be replaced by the
    real Laravel workflows in later phases.
--}}
<x-layouts.fluent :title="$pageTitle . ' — Fluent'" :description="$lead" :noindex="true">
  <section class="fhead" aria-labelledby="p-h">
    <div class="fhead-bg" aria-hidden="true">
      <div class="fhead-grid"></div>
      <div class="fhead-ghost"><svg viewBox="0 0 990 500"><use href="#fl-mark"/></svg></div>
      <div class="fhead-veil"></div>
    </div>
    <div class="wrap fhead-in">
      <a class="backlink rise" href="{{ route('home') }}" style="animation-delay:.05s">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        العودة إلى الرئيسية
      </a>
      <span class="eyebrow rise" style="animation-delay:.12s">{{ $eyebrow }}</span>
      <h1 class="d2 rise" id="p-h" style="animation-delay:.24s">{{ $pageTitle }}</h1>
      <p class="lead rise" style="animation-delay:.36s">{{ $lead }}</p>
    </div>
  </section>

  <section class="fbody" aria-labelledby="soon-h">
    <div class="wrap">
      <div class="fcard reveal" style="max-width:780px">
        <div class="soon">
          <span class="soon-tag">قيد التجهيز</span>
          <h2 id="soon-h">{{ $soonTitle }}</h2>
          <p>{{ $soonText }}</p>
          <div class="soon-actions">
            <a class="btn btn-primary" href="{{ route('home') }}">
              العودة إلى الرئيسية
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-layouts.fluent>
