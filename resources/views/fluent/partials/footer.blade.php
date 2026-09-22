{{--
    Footer — structure from the approved prototype.
    Logo, email, phone and social links come from Filament → Settings.
--}}
@php
    $home      = request()->routeIs('home') ? '' : route('home');
    $address   = collect($setting?->Address ?? []);
    $ftEmail   = $address->firstWhere('social_type', 'email')['name'] ?? null;
    $ftPhone   = $address->firstWhere('social_type', 'phone')['name'] ?? null;
    $ftLogo    = $setting?->footerlogo ?: $setting?->headerlogo;
    $ftSocials = collect($setting?->social_links ?? [])->filter(fn ($l) => filled($l['name'] ?? null));
    $socialLabels = [
        'Facebook' => 'Facebook', 'WhatsApp' => 'WhatsApp', 'Instagram' => 'Instagram',
        'Twitter' => 'X', 'LinkedIn' => 'LinkedIn', 'Snapchat' => 'Snapchat',
        'YouTube' => 'YouTube', 'TikTok' => 'TikTok', 'Telegram' => 'Telegram',
    ];
@endphp
<footer class="ft">
  <div class="wrap">
    <div class="ft-top">
      <div>
        <a class="logo" href="{{ route('home') }}" aria-label="فلوانت Fluent">
          @if ($ftLogo)
            <img class="ft-logo-img" src="{{ asset('storage/' . $ftLogo) }}" alt="شعار فلوانت Fluent" loading="lazy" decoding="async">
          @else
            <span class="logo-mark"><svg viewBox="0 0 990 500" aria-hidden="true"><use href="#fl-mark"/></svg></span>
            <span class="logo-word">Fluent</span>
          @endif
        </a>
        <p class="ft-about">منصة للمحاكاة المهنية والتجارب العملية، تضع الطلاب والخريجين في بيئات تحاكي واقع العمل.</p>
        @if ($ftSocials->isNotEmpty())
          <div class="socials" aria-label="حسابات Fluent">
            @foreach ($ftSocials as $link)
              <a href="{{ $link['name'] }}" target="_blank" rel="noopener" dir="ltr">{{ $socialLabels[$link['social_type']] ?? $link['social_type'] }}</a>
            @endforeach
          </div>
        @endif
      </div>
      <nav aria-labelledby="ft-nav-h">
        <h2 class="ft-h" id="ft-nav-h">تنقل</h2>
        <div class="ft-links">
          <a href="{{ $home }}#about">عن فلوانت</a>
          <a href="{{ $home }}#how">كيف تعمل</a>
          <a href="{{ $home }}#experience">التجربة</a>
          <a href="{{ $home }}#audience">لمن؟</a>
          <a href="{{ $home }}#contact">ابدأ من هنا</a>
        </div>
      </nav>
      <div>
        <h2 class="ft-h">تواصل</h2>
        <div class="ft-links">
          <a href="{{ route('fluent.apply') }}">سجّل في المحاكاة</a>
          <a href="{{ route('fluent.challenge') }}">شاركنا تحديًا</a>
          <a href="{{ route('fluent.portal') }}">مساحتي في Fluent</a>
          <a href="{{ $home }}#contact-form">راسلنا</a>
          @if ($ftEmail)
            <a href="mailto:{{ $ftEmail }}" dir="ltr" style="text-align:right">{{ $ftEmail }}</a>
          @endif
          @if ($ftPhone)
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $ftPhone) }}" dir="ltr" style="text-align:right">{{ $ftPhone }}</a>
          @endif
        </div>
      </div>
    </div>
    <div class="ft-bot">
      <span><span dir="ltr" class="num">© {{ now()->format('Y') }} Fluent</span> — جميع الحقوق محفوظة.</span>
      <span>المملكة العربية السعودية</span>
    </div>
  </div>
</footer>
