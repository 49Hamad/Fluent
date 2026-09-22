{{--
    05b Partners — marquee markup and motion from the approved prototype.
    Logos come from Filament → "العملاء" (Client, is_active = true).
    Transparent formats (png / svg / webp) get the prototype's single-tone
    light treatment. JPG logos have a solid background, so the filter would
    turn them into white blocks — they are shown in their original colours.
--}}
@if ($clients->isNotEmpty())
<section class="sec partners" id="partners" aria-labelledby="partners-h">
  <div class="wrap partners-head reveal">
    <h2 class="eyebrow" id="partners-h">{{ $partnersTitle }}</h2>
  </div>

  <div class="mq reveal" style="--d:100ms" data-marquee dir="ltr">
    <ul class="mq-track" id="partners-track" aria-label="شعارات شركاء Fluent">
      @foreach ($clients as $client)
        @php
            $ext = strtolower(pathinfo((string) $client->logo, PATHINFO_EXTENSION));
            $mono = in_array($ext, ['png', 'svg', 'webp', 'gif']);
        @endphp
        <li class="plogo">
          @if (filled($client->link) && $client->link !== '#')
            <a href="{{ $client->link }}" target="_blank" rel="noopener" aria-label="{{ $client->name }}">
          @endif
          <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" decoding="async"
               style="--h:clamp(38px,4.4vw,56px){{ $mono ? '' : ';filter:none;border-radius:8px' }}">
          @if (filled($client->link) && $client->link !== '#')
            </a>
          @endif
        </li>
      @endforeach
    </ul>
  </div>
</section>
@endif
