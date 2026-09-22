{{--
    05c Evidence — markup and motion from the approved prototype.
    Numbers come from Filament → "أرقام تتحدث" (NumberTalk counters).
    The first counter is the hero number (larger, Fluent yellow), exactly
    like the prototype. Hidden when no counters are managed.
--}}
@php
    $counters = collect($numberTalk?->counters ?? [])
        ->filter(fn ($c) => filled($c['title'] ?? null))
        ->values();
    $n = $counters->count();
    $rest = max($n - 1, 0);            // items after the hero number
    $r3 = $rest % 3;                   // tablet: 3 columns
    $r2 = $rest % 2;                   // mobile: 2 columns
@endphp
@if ($n)
<section class="sec proof" id="proof" aria-labelledby="proof-h">
  <div class="wrap">
    <div class="proof-head">
      <div class="reveal">
        <span class="eyebrow">الدليل</span>
        <h2 class="d2" id="proof-h">محاكاة واحدة، أرقام حقيقية.</h2>
      </div>
      <span class="proof-note reveal" style="--d:80ms">من محاكاة Fluent الأولى</span>
    </div>

    <div class="proof-grid reveal" style="--d:120ms;--cols:{{ $n + 1 }}">
      @foreach ($counters as $i => $counter)
        @php
            $classes = ['pstat'];
            if ($i === 0) $classes[] = 'is-main';
            if ($i === $n - 1 && $i > 0) {
                if ($r3 === 2) $classes[] = 'span-3-2';
                if ($r3 === 1) $classes[] = 'span-3-3';
                if ($r2 === 1) $classes[] = 'span-2-2';
            }
        @endphp
        <div class="{{ implode(' ', $classes) }}">
          <span class="pstat-v num" data-count="{{ (int) ($counter['numbers'] ?? 0) }}">0</span>
          <span class="pstat-k">{{ $counter['title'] }}</span>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif
