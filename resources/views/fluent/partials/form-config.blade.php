{{--
    Prototype form engine configuration (assets/js/fluent-config.js in the ZIP),
    adapted for Laravel frontend review mode:
      - previewMode is ALWAYS true here → the engine validates and shows the
        confirmation screen, but sends/uploads/stores nothing.
      - Supabase is NOT used (left empty on purpose). The real submission will
        go to a Laravel endpoint in a later phase.
      - No localStorage data layer (FluentStore) is loaded.
--}}
@php
    $cfgEmail = \App\Support\FluentContact::email();
@endphp
<script>
window.FLUENT_CONFIG = {
  supabase: {},            /* not used — Laravel backend comes later */
  endpoint: { mode: 'none' },
  tables: { student: 'student', challenge: 'challenge' },
  registration: { student: 'open', challenge: 'open' },
  registrationCopy: {
    open:     { badge: 'التسجيل مفتوح الآن' },
    waitlist: {
      badge: 'التسجيل مغلق — قائمة الانتظار مفتوحة',
      cardTitle: 'قائمة الانتظار',
      title: 'التسجيل على هذه الدورة مغلق حاليًا.',
      text:  'سجّل في قائمة الانتظار وسنراسلك أول ما تُفتح الدورة القادمة، قبل الإعلان العام.'
    },
    closed: {
      badge: 'التسجيل مغلق حاليًا',
      cardTitle: 'التسجيل مغلق',
      title: 'الدورة الحالية اكتمل تسجيلها.',
      text:  'نفتح التسجيل على دفعات محدودة. اترك بياناتك وسنراسلك أول ما تُفتح الدورة القادمة.',
      notifyCta:   'نبّهني عند فتح التسجيل',
      notifyTitle: 'نبّهني عند فتح التسجيل',
      notifyText:  'ثلاث خانات فقط. نستخدمها لتنبيهك، ولا شيء غير ذلك.'
    }
  },
  contactEmail: @json($cfgEmail),
  homeHref: @json(route('home')),
  botProtection: { provider: 'none', siteKey: '' },
  previewMode: true,       /* frontend review mode — nothing is sent or stored */
  minFillSeconds: 4
};
</script>
