{{--
    شاركنا تحديًا — approved prototype UI (challenge.html in fluent-site-updated.zip).
    Markup copied from the prototype; only page links and the contact
    e-mail are now Laravel-driven. The form itself is built by the
    prototype engine (fluent-forms.js) from fluent-schemas.js.

    FRONTEND REVIEW MODE: the engine runs with previewMode = true, so it
    validates locally and shows the confirmation screen but sends, uploads
    and stores NOTHING. The Laravel submission backend is a later phase.
--}}
@php
    $contactEmail = \App\Support\FluentContact::email();   // managed in Filament
@endphp
<x-layouts.fluent :title="'شاركنا تحديًا — Fluent'" description="شارك تحديًا أو مشروعًا حقيقيًا من جهتك ليعمل عليه المشاركون في محاكاة Fluent المهنية، وتستلم مخرجات مفيدة." :noindex="true" :site-script="false">
<!-- ==================== رأس الصفحة ==================== -->
<section class="fhead" aria-labelledby="p-h">
  <div class="fhead-bg" aria-hidden="true">
    <div class="fhead-grid"></div>
    <div class="fhead-ghost"><svg viewBox="0 0 990 500"><use href="#fl-mark"/></svg></div>
    <div class="fhead-veil"></div>
  </div>
  <div class="wrap fhead-in">
    <a class="backlink rise" href="{{ route('home') }}" style="animation-delay:.05s">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      العودة إلى الرئيسية
    </a>
    <span class="eyebrow rise" style="animation-delay:.12s">للشركات والجهات</span>
    <h1 class="d2 rise" id="p-h" style="animation-delay:.24s">شاركنا تحديًا</h1>
    <p class="lead rise" style="animation-delay:.36s">عندكم مشكلة حقيقية أو مشروع يستحق التفكير؟ حوّلوه إلى تحدٍّ داخل محاكاة Fluent. تعمل عليه فرق من الطلاب والخريجين بأدوار ومواعيد تسليم، وتستلمون مخرجات مكتوبة من زوايا متعدّدة.</p>
    <span class="fstatus rise" id="reg-status" style="animation-delay:.48s" hidden></span>
  </div>
</section>

<!-- ==================== النموذج ==================== -->
<section class="fbody" id="form" aria-labelledby="form-h">
  <div class="wrap flayout">

    <div class="fcard reveal">
      <h2 id="form-h" class="d3" style="margin-bottom:1.6rem">تفاصيل التحدي</h2>
      <!-- يبنيه محرّك النماذج من assets/js/fluent-schemas.js -->
      <div id="fluent-form"></div>
      <noscript>
        <p class="fhelp">هذا النموذج يحتاج تفعيل JavaScript. إن تعذّر ذلك راسلنا مباشرة على
          <a href="mailto:{{ $contactEmail }}" dir="ltr" style="color:var(--signal)">{{ $contactEmail }}</a>.</p>
      </noscript>
    </div>

    <aside class="faside reveal" style="--d:120ms">
      <div class="fnote">
        <h2 class="fnote-h">كيف تسير الشراكة</h2>
        <ol>
          <li><b>ترسلون التحدي</b>مشكلة أو مشروع حقيقي من واقع عملكم.</li>
          <li><b>نراجعه معكم</b>نتواصل لتحديد النطاق وما يمكن مشاركته.</li>
          <li><b>يعمل عليه المشاركون</b>ضمن فرق، بأدوار ومواعيد تسليم.</li>
          <li><b>تستلمون المخرجات</b>أفكار ومقترحات مكتوبة من زوايا متعدّدة.</li>
        </ol>
      </div>
      <div class="fnote">
        <h2 class="fnote-h">ما الذي يصلح كتحدٍّ</h2>
        <ul class="chips" style="margin-top:-.2rem">
          <li class="chip">مشكلة تشغيلية</li>
          <li class="chip">تحسين تجربة عميل</li>
          <li class="chip">فكرة منتج</li>
          <li class="chip">حملة أو رسالة</li>
          <li class="chip">عملية داخلية</li>
          <li class="chip">تحدٍّ مجتمعي</li>
        </ul>
        <p style="margin-top:1.2rem">لا يشترط أن يكون التحدي كبيرًا. الأهم أن يكون حقيقيًا وواضحًا.</p>
      </div>
      <div class="fnote">
        <h2 class="fnote-h">السرّية</h2>
        <p>لا نطلب بيانات سرّية في هذا النموذج. إذا كان التحدي حسّاسًا، علّم الخيار المخصّص وسنناقش حدود المشاركة قبل أي اعتماد.</p>
        <p style="margin-top:1rem">للاستفسار: <a href="mailto:{{ $contactEmail }}" dir="ltr">{{ $contactEmail }}</a></p>
      </div>
    </aside>

  </div>
</section>

@push('scripts')
  @include('fluent.partials.form-config')
  <script src="{{ asset('fluent/js/fluent-schemas.js') }}"></script>
  <script src="{{ asset('fluent/js/fluent-forms.js') }}"></script>
  <script>
    FluentForm.shell();
    FluentForm.init('challenge', '#fluent-form', '#reg-status');
  </script>
@endpush
</x-layouts.fluent>
