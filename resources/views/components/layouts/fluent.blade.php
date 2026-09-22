{{--
    Fluent public layout (redesign).
    Used by the new homepage (ShowHomePage) and the new public pages
    (student application, business challenge, student login, portal preview).
    The old layout (components/layouts/app.blade.php) is untouched and still
    serves /feedback-form/{id}.

    Keeps from the old layout:
      - SEO fields managed in Filament → Settings (meta title/description/keywords/image)
      - Tracking scripts managed in Filament → AddingScript (status = 1)
--}}
@props([
    'title' => null,
    'description' => null,
    'noindex' => false,
    'chrome' => true,      // site header + footer (false for the login / portal app screens)
    'siteScript' => true,  // homepage motion script; form pages use FluentForm.shell() instead
    'appCss' => false,     // prototype app layer (login / portal)
])
@php
    $fluentSetting   = \App\Models\Setting::first();
    $fluentScripts   = \App\Models\AddingScript::where('status', 1)->get();

    $metaTitle       = $title ?? ($fluentSetting?->meta_title ?: 'Fluent — تجربة العمل تبدأ قبل الوظيفة');
    $metaDescription = $description ?? ($fluentSetting?->meta_description ?: 'Fluent منصة للمحاكاة المهنية والتجارب العملية، تضع الطلاب والخريجين في بيئات تحاكي واقع العمل ليبنوا مهاراتهم من خلال الممارسة والتعاون وحل التحديات الحقيقية.');
    $metaKeywords    = is_array($fluentSetting?->meta_keywords) ? implode('، ', $fluentSetting->meta_keywords) : null;
    $metaImage       = $fluentSetting?->meta_image ? asset('storage/' . $fluentSetting->meta_image) : null;

    $fluentEmail = collect($fluentSetting?->Address ?? [])->firstWhere('social_type', 'email')['name'] ?? null;
    $v = '?v=' . (@filemtime(public_path('fluent/css/fluent-laravel.css')) ?: '1');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
@if ($metaKeywords)
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
<meta name="author" content="فلوانت">
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">
<meta name="language" content="ar">
<meta name="theme-color" content="#0f0f0f">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:locale" content="ar_SA">
<meta property="og:url" content="{{ url()->current() }}">
@if ($metaImage)
<meta property="og:image" content="{{ $metaImage }}">
<meta name="twitter:card" content="summary_large_image">
@endif
<link rel="icon" href="{{ asset('front/assets/images/Fluent-LogoMark-Colored-RGB.png') }}" type="image/png">

<link rel="preload" href="{{ asset('fluent/fonts/alexandria-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('fluent/css/fluent-site.css') }}{{ $v }}">
<link rel="stylesheet" href="{{ asset('fluent/css/fluent-forms.css') }}{{ $v }}">
@if ($appCss)
<link rel="stylesheet" href="{{ asset('fluent/css/fluent-app.css') }}{{ $v }}">
@endif
<link rel="stylesheet" href="{{ asset('fluent/css/fluent-laravel.css') }}{{ $v }}">

{{-- Tracking / custom scripts managed in Filament (unchanged behaviour) --}}
@foreach ($fluentScripts as $script)
{!! $script->script !!}
@endforeach
@stack('head')
</head>
<body>
@include('fluent.partials.logo-symbol')
@if ($chrome)
<a class="skip" href="#main">تجاوز إلى المحتوى</a>
@include('fluent.partials.header')

<main id="main">
{{ $slot }}
</main>

@include('fluent.partials.footer', ['setting' => $fluentSetting])
@else
{{ $slot }}
@endif

@if ($siteScript)
<script src="{{ asset('fluent/js/fluent-site.js') }}{{ $v }}" defer></script>
@endif
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Fluent',
    'alternateName' => 'فلوانت',
    'url' => url('/'),
    'description' => 'منصة للمحاكاة المهنية والتجارب العملية، تضع الطلاب والخريجين في بيئات تحاكي واقع العمل.',
    'email' => $fluentEmail,
    'areaServed' => 'SA',
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@stack('scripts')
</body>
</html>
