{{--
    سجّل في المحاكاة — approved prototype UI (apply.html in fluent-site-updated.zip).
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
<x-layouts.fluent :title="'سجّل في المحاكاة — Fluent'" description="سجّل في تجربة المحاكاة المهنية من Fluent: بيئة عمل محاكية، فريق، أدوار، ومشروع بمواعيد تسليم حقيقية." :noindex="true" :site-script="false">
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
    <span class="eyebrow rise" style="animation-delay:.12s">للطلاب والخريجين</span>
    <h1 class="d2 rise" id="p-h" style="animation-delay:.24s">سجّل في المحاكاة</h1>
    <p class="lead rise" style="animation-delay:.36s">تنضم إلى بيئة عمل محاكية: فريق، دور محدّد، مشروع، ومواعيد تسليم. تخرج بخبرة عملية تقدر تتكلم عنها بثقة — قبل وظيفتك الأولى.</p>
    <span class="fstatus rise" id="reg-status" style="animation-delay:.48s" hidden></span>
  </div>
</section>

<!-- ==================== النموذج ==================== -->
<section class="fbody" id="form" aria-labelledby="form-h">
  <div class="wrap flayout">

    <div class="fcard reveal">
      <h2 id="form-h" class="d3" style="margin-bottom:1.6rem">بيانات التسجيل</h2>
      <!-- يبنيه محرّك النماذج من assets/js/fluent-schemas.js -->
      <div id="fluent-form"></div>
      <noscript>
        <p class="fhelp">هذا النموذج يحتاج تفعيل JavaScript. إن تعذّر ذلك راسلنا مباشرة على
          <a href="mailto:{{ $contactEmail }}" dir="ltr" style="color:var(--signal)">{{ $contactEmail }}</a>.</p>
      </noscript>
    </div>

    <aside class="faside reveal" style="--d:120ms">
      <div class="fnote" data-when="open">
        <h2 class="fnote-h">بعد ما ترسل</h2>
        <ol>
          <li><b>مراجعة الطلب</b>نقرأ كل طلب بأنفسنا، ما فيه فرز آلي.</li>
          <li><b>رد على بريدك</b>يصلك القبول أو الاعتذار خلال أيام عمل قليلة.</li>
          <li><b>تفاصيل التجربة</b>المقبولون يستلمون الموعد والدور وتفاصيل الفريق.</li>
        </ol>
      </div>
      <div class="fnote">
        <h2 class="fnote-h">سجّلت من قبل؟</h2>
        <p>لك مساحة في Fluent تتابع منها حالة طلبك.</p>
        <p style="margin-top:.9rem"><a href="{{ route('fluent.login') }}" style="color:var(--signal)">ادخل مساحتك ←</a></p>
      </div>
      <div class="fnote" data-when="open">
        <h2 class="fnote-h">قبل ما تبدأ</h2>
        <p>التعبئة تاخذ أقل من خمس دقائق. أجب بصدق — ما نبحث عن سيرة ذاتية مثالية، نبحث عن شخص جاهز يشتغل ضمن فريق.</p>
        <p style="margin-top:1rem">سؤال قبل التسجيل؟ راسلنا على <a href="mailto:{{ $contactEmail }}" dir="ltr">{{ $contactEmail }}</a>.</p>
      </div>
      <div class="fnote">
        <h2 class="fnote-h">عندك تحدٍّ بدل ذلك؟</h2>
        <p>إذا كنت تمثّل شركة أو جهة وعندك مشكلة حقيقية تصلح لمحاكاة مهنية:</p>
        <p style="margin-top:.9rem"><a href="{{ route('fluent.challenge') }}" style="color:var(--signal)">شاركنا تحديًا ←</a></p>
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
    FluentForm.init('student', '#fluent-form', '#reg-status');
  </script>
@endpush
</x-layouts.fluent>
