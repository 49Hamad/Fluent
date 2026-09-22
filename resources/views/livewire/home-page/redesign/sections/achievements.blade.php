{{--
    Achievements & milestones — content from Filament → "الإنجازات" (Achievement).
    Not part of the prototype; built only from prototype components
    (eyebrow + d2 + lead head, journey-style milestones, bridge-style line draw).
--}}
@php
    $milestones = collect($achievement?->achievements ?? [])
        ->filter(fn ($a) => filled($a['title'] ?? null))
        ->values();
@endphp
@if ($achievement && $milestones->isNotEmpty())
<section class="sec ach" id="achievements" aria-labelledby="ach-h">
  <div class="wrap">
    <div class="ach-head">
      <div class="reveal">
        <span class="eyebrow">الإنجازات والمحطات</span>
        <h2 class="d2" id="ach-h">{{ $achievement->title }}</h2>
      </div>
      @if (filled($achievement->description))
        <p class="lead reveal" style="--d:120ms">{{ $achievement->description }}</p>
      @endif
    </div>

    <ol class="miles" style="--n:{{ min($milestones->count(), 4) }}" aria-label="محطات Fluent">
      @foreach ($milestones as $i => $item)
        <li class="mile reveal" style="--d:{{ $i * 90 }}ms;--md:{{ 300 + $i * 260 }}ms">
          <span class="mile-dot" aria-hidden="true"></span>
          <span class="mile-k num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
          <h3 class="mile-t">{{ $item['title'] }}</h3>
        </li>
      @endforeach
    </ol>
  </div>
</section>
@endif
