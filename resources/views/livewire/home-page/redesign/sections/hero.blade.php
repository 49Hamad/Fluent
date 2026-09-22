{{-- 01 Hero — markup from the approved prototype (index.html). --}}
<section class="hero" aria-labelledby="hero-h">
  <div class="hero-bg" aria-hidden="true">
    <div class="hero-grid"></div>
    <div class="hero-ghost"><svg viewBox="0 0 990 500"><use href="#fl-mark"/></svg></div>
    <canvas class="hero-canvas" id="wave"></canvas>
    <div class="hero-veil"></div>
  </div>

  <div class="wrap hero-in">
    <div class="hero-copy">
      <span class="eyebrow rise" style="animation-delay:.1s">محاكاة مهنية · تجارب عملية</span>
      <h1 class="d1 rise" id="hero-h" style="animation-delay:.22s">
        تجربة العمل تبدأ <span class="hl">قبل الوظيفة.</span>
      </h1>
      <p class="lead rise" style="animation-delay:.36s">
        <b style="font-weight:500;color:var(--paper)">Fluent</b> منصة للمحاكاة المهنية والتجارب العملية، تضع الطلاب والخريجين في بيئات تحاكي واقع العمل، ليبنوا مهاراتهم من خلال الممارسة، التعاون، وحل التحديات الحقيقية.
      </p>
      <div class="hero-actions rise" style="animation-delay:.5s">
        <a class="btn btn-primary" href="#about">
          اكتشف Fluent
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        </a>
        <a class="btn btn-ghost" href="{{ route('fluent.challenge') }}">شاركنا تحديًا</a>
      </div>
    </div>
  </div>

  <div class="hero-cue" aria-hidden="true"><i></i><span>Scroll</span></div>
</section>
