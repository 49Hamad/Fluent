{{-- Header — markup from the approved prototype. Anchors work from any page. --}}
@php $home = request()->routeIs('home') ? '' : route('home'); @endphp
<header class="hdr" id="hdr">
  <div class="wrap hdr-in">
    <a class="logo" href="{{ route('home') }}" aria-label="فلوانت Fluent — الصفحة الرئيسية">
      <span class="logo-mark"><svg viewBox="0 0 990 500" aria-hidden="true"><use href="#fl-mark"/></svg></span>
      <span class="logo-word">Fluent</span>
    </a>
    <nav class="hdr-nav" aria-label="التنقل الرئيسي">
      <a href="{{ $home }}#about">عن فلوانت</a>
      <a href="{{ $home }}#how">كيف تعمل</a>
      <a href="{{ $home }}#experience">التجربة</a>
      <a href="{{ $home }}#audience">لمن؟</a>
    </nav>
    <a class="btn btn-ghost hdr-cta" href="{{ route('fluent.apply') }}">سجّل في المحاكاة</a>
  </div>
</header>
