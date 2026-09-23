{{--
    Prototype form engine configuration (assets/js/fluent-config.js in the ZIP),
    adapted for Laravel. Supabase and localStorage are NOT used.

    Variables (all optional):
      $formLive      true  → real submission to Laravel ($formEndpoint)   [student application, challenge]
                     false → review mode: validates, shows the confirmation
                             screen, sends/stores nothing (e.g. apply page with no cohort)
      $formEndpoint  POST URL for the live mode
      $formExtra     extra fields sent with the form (e.g. cohort_id)
      $studentRegistration  open | waitlist | closed  (from Filament → الدفعات)
      $openBadge     text of the "open" badge above the form (default: التسجيل مفتوح الآن)
--}}
@php
    $cfgEmail = \App\Support\FluentContact::email();
    $cfgLive = (bool) ($formLive ?? false);
    $cfgEndpoint = $cfgLive
        ? ['mode' => 'laravel', 'url' => $formEndpoint ?? '', 'csrf' => csrf_token(), 'extra' => (object) ($formExtra ?? [])]
        : ['mode' => 'none'];
    $cfgStudentReg = $studentRegistration ?? 'open';
    $cfgOpenBadge = $openBadge ?? 'التسجيل مفتوح الآن';
@endphp
<script>
window.FLUENT_CONFIG = {
  supabase: {},            /* not used */
  endpoint: {!! json_encode($cfgEndpoint, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!},
  tables: { student: 'student', challenge: 'challenge' },
  registration: { student: @json($cfgStudentReg), challenge: 'open' },
  registrationCopy: {
    open:     { badge: @json($cfgOpenBadge) },
    waitlist: {
      badge: 'التسجيل مغلق — قائمة الانتظار مفتوحة',
      cardTitle: 'قائمة الانتظار',
      title: 'مقاعد هذه الدفعة شبه مكتملة.',
      text:  'تقدر تقدّم الآن بنفس النموذج، وسيُضاف طلبك إلى قائمة الانتظار ونراسلك بأي تحديث.'
    },
    closed: {
      badge: 'التسجيل مغلق حاليًا',
      cardTitle: 'التسجيل مغلق',
      title: 'الدورة الحالية اكتمل تسجيلها.',
      text:  'نفتح التسجيل على دفعات محدودة. تابعنا أو راسلنا لمعرفة موعد الدفعة القادمة.',
      notifyCta:   'نبّهني عند فتح التسجيل',
      notifyTitle: 'نبّهني عند فتح التسجيل',
      notifyText:  'ثلاث خانات فقط. نستخدمها لتنبيهك، ولا شيء غير ذلك.'
    }
  },
  contactEmail: @json($cfgEmail),
  homeHref: @json(route('home')),
  botProtection: { provider: 'none', siteKey: '' },
  previewMode: @json(! $cfgLive),  /* true = frontend review mode (nothing sent or stored) */
  minFillSeconds: 4
};
</script>
