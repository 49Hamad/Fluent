{{-- 08 Final CTA — markup from the approved prototype (index.html). --}}
<section class="sec cta" id="contact" aria-labelledby="cta-h">
  <div class="cta-ghost" aria-hidden="true"><svg viewBox="0 0 990 500"><use href="#fl-mark"/></svg></div>
  <div class="wrap cta-in">
    <span class="eyebrow cta-eyebrow reveal">ابدأ من هنا</span>
    <h2 class="d2 reveal" id="cta-h" style="--d:80ms">جاهز تعيش تجربة العمل؟</h2>
    <p class="cta-sub reveal" style="--d:160ms">اختر مسارك وابدأ.</p>

    <div class="cta-paths">
      <article class="cpath reveal" style="--d:240ms">
        <span class="cpath-k">للطلاب والخريجين</span>
        <h3 class="cpath-t">عِش التجربة</h3>
        <p class="cpath-d">تنضم إلى بيئة عمل محاكية ضمن فريق، بدور ومشروع ومواعيد تسليم، وتخرج بخبرة تسبق وظيفتك الأولى.</p>
        <a class="btn btn-dark" href="{{ route('fluent.apply') }}">
          سجّل في المحاكاة
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        </a>
      </article>

      <article class="cpath reveal" style="--d:320ms">
        <span class="cpath-k">للشركات والجهات</span>
        <h3 class="cpath-t">قدّم تحديًا حقيقيًا</h3>
        <p class="cpath-d">تحوّل مشكلة أو مشروعًا من واقع عملكم إلى تحدٍّ تشتغل عليه فرق المحاكاة، وتستلمون مخرجات مكتوبة.</p>
        <a class="btn btn-onlight" href="{{ route('fluent.challenge') }}">
          شاركنا تحديًا
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        </a>
      </article>
    </div>
  </div>
</section>
