{{--
    Testimonials — from the existing customer feedback system
    (FormEvaluation with is_active = true, toggled in Filament).
    Not part of the prototype; shown only when at least one is approved.
--}}
@if ($testimonials->isNotEmpty())
<section class="sec tst" id="testimonials" aria-labelledby="tst-h">
  <div class="wrap">
    <div class="tst-head reveal">
      <span class="eyebrow">بشهادة شركائنا</span>
      <h2 class="d2" id="tst-h">آراء عملائنا</h2>
    </div>
    <div class="tst-row reveal" style="--d:100ms" tabindex="0" aria-label="آراء العملاء">
      @foreach ($testimonials as $t)
        <figure class="tcard">
          <span class="tcard-q" aria-hidden="true"><svg viewBox="0 0 990 500"><use href="#fl-mark"/></svg></span>
          <blockquote>{{ $t->feedback }}</blockquote>
          <figcaption>
            <span class="tcard-n">{{ $t->client_name }}</span>
            @if (filled($t->company_name))
              <span class="tcard-c">{{ $t->company_name }}</span>
            @endif
          </figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
@endif
