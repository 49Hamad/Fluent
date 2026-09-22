/* ============================================================
   Copied from the approved prototype (assets/js/fluent-portal.js).
   Only change: prototype page links (*.html) → Laravel routes.
   ------------------------------------------------------------
   FLUENT — بوابة الطالب «مساحتي في Fluent»
   ------------------------------------------------------------
   تقرأ كل شيء عبر FluentStore. لا تعرف شيئًا عن مصدر البيانات،
   فاستبدال طبقة المعاينة بـ Supabase لا يمسّ هذا الملف.

   🔒 الطالب يرى طلبه هو فقط. الملاحظات الداخلية لا تُقرأ هنا
      إطلاقًا — لا تُطلب ولا تُعرض. عند الإنتاج تمنعها RLS أصلًا.
   ============================================================ */

(function () {
  'use strict';

  var view  = document.getElementById('pview');
  var who   = document.getElementById('who');
  var nav   = document.getElementById('pnav');
  var out   = document.getElementById('signout');
  var bar   = document.getElementById('demobar');
  if (!view) return;

  var session = FluentStore.session('student');
  if (!session || session.role !== 'student') { FluentNav.replace('/login'); return; }

  var current = 'overview';
  var app = null;

  var ICON = {
    doc:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a1.5 1.5 0 0 0-1.5 1.5v15A1.5 1.5 0 0 0 7 21h10a1.5 1.5 0 0 0 1.5-1.5V7.5z"/><path d="M14 3v4.5h4.5"/></svg>',
    arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>'
  };


  /* يُبقي العنصر النشط مرئيًا حين يكون شريط التنقّل قابلًا للتمرير */
  function scrollActiveNav() {
    var on = nav.querySelector('.is-on');
    if (on && on.scrollIntoView) {
      try { on.scrollIntoView({ inline: 'nearest', block: 'nearest' }); } catch (e) {}
    }
  }

  function esc(v) { return String(v == null ? '' : v); }

  function fmtDate(iso) {
    if (!iso) return '—';
    try {
      return new Date(iso).toLocaleDateString('ar-SA-u-ca-gregory-nu-latn', { year: 'numeric', month: 'long', day: 'numeric' });
    } catch (e) { return iso; }
  }

  function firstName(full) { return String(full || '').trim().split(/\s+/)[0] || ''; }

  if (window.FLUENT_CONFIG && FLUENT_CONFIG.previewMode) {
    bar.hidden = false;
    bar.innerHTML = '<span><b>نسخة تجريبية.</b> البيانات المعروضة للمعاينة فقط.</span>';
  }

  who.textContent = session.name || '';
  out.addEventListener('click', function () {
    FluentStore.signOut('student').then(function () { FluentNav.go('/'); });
  });

  nav.addEventListener('click', function (e) {
    var b = e.target.closest('button[data-view]');
    if (!b) return;
    current = b.dataset.view;
    Array.prototype.forEach.call(nav.children, function (n) { n.classList.toggle('is-on', n === b); });
    scrollActiveNav();
    render();
  });

  /* ============================================================
     مسار الحالة
     ============================================================ */
  function track(status) {
    var steps = FluentStore.TRACK.map(function (id) {
      return FluentStore.statusMeta(FluentStore.APP_STATUS, id);
    });

    /* الحالات النهائية: المسار مكتمل والنتيجة تُعرض تحته */
    var terminal = ['accepted', 'rejected', 'waitlist'].indexOf(status) > -1;
    var idx = FluentStore.TRACK.indexOf(status);

    var html = '<div class="track"><span class="kicker">مسار الطلب</span><div class="track-steps">';
    steps.forEach(function (s, i) {
      var cls = '';
      if (terminal) cls = 'is-done';
      else if (i < idx) cls = 'is-done';
      else if (i === idx) cls = 'is-on';
      html += '<div class="tstep ' + cls + '"><span class="tstep-l">' + esc(s.label) + '</span></div>';
    });

    /* الخطوة الأخيرة = النتيجة */
    var last = terminal
      ? FluentStore.statusMeta(FluentStore.APP_STATUS, status).label
      : 'النتيجة النهائية';
    html += '<div class="tstep ' + (terminal ? 'is-on' : '') + '"><span class="tstep-l">' + esc(last) + '</span></div>';
    html += '</div>';

    html += outcome(status);
    return html + '</div>';
  }

  /* نصوص النتيجة — الرفض مصاغ باحترام وبلا لون قاسٍ */
  function outcome(status) {
    if (status === 'accepted') {
      return '<div class="outcome t-good"><b>مقبول</b>' +
        'تفاصيل التجربة في الأعلى. نراك قريبًا.</div>';
    }
    if (status === 'waitlist') {
      return '<div class="outcome t-hold"><b>على قائمة الانتظار</b>' +
        'طلبك اجتاز المراجعة، لكن مقاعد هذه الدفعة اكتملت. إذا فُتح مقعد أو بدأت دفعة جديدة، ' +
        'تصلك رسالة قبل الإعلان العام.</div>';
    }
    if (status === 'rejected') {
      return '<div class="outcome"><b>اكتمل النظر في طلبك لهذه الدفعة</b>' +
        'ما تمكّنا من ترشيحك للدفعة الحالية. هذا لا يعني أن ملفك ضعيف — المقاعد محدودة ' +
        'والاختيار مرتبط بطبيعة تحديات كل دفعة. نرحّب بطلبك في الدفعات القادمة.</div>';
    }
    if (status === 'interview') {
      return '<div class="outcome"><b>مرشح للمقابلة</b>' +
        'وصلك ترشيح لمقابلة قصيرة. سنراسلك على بريدك لتحديد الموعد.</div>';
    }
    if (status === 'review') {
      return '<div class="outcome"><b>قيد المراجعة</b>' +
        'فريق Fluent يقرأ طلبك الآن. المراجعة تستغرق عادة أيام عمل قليلة.</div>';
    }
    return '<div class="outcome"><b>تم استلام طلبك</b>' +
      'طلبك في قائمة المراجعة. سنخبرك بأي تحديث على بريدك.</div>';
  }

  /* ============================================================
     لوحة القبول
     ============================================================ */
  function acceptance(cohort) {
    cohort = cohort || {};
    var rows = [
      ['اسم الدفعة',   cohort.name],
      ['تاريخ البداية', cohort.startDate],
      ['الوقت',        cohort.time],
      ['الموقع',       cohort.location]
    ];

    var html =
      '<section class="welcome">' +
        '<span class="welcome-mark" aria-hidden="true"><svg viewBox="0 0 990 500"><use href="#fl-mark"/></svg></span>' +
        '<h2>مرحبًا بك في تجربة Fluent</h2>' +
        '<p>طلبك مقبول. هذه تفاصيل دفعتك — نحدّثها هنا أولًا بأول، فارجع لهذه الصفحة قبل موعد البداية.</p>' +
        '<dl class="cohort">';

    rows.forEach(function (r) {
      var v = String(r[1] || '').trim();
      html += '<div><dt>' + esc(r[0]) + '</dt><dd class="' + (v ? '' : 'tbd') + '">' +
              esc(v || 'يُعلن لاحقًا') + '</dd></div>';
    });
    html += '</dl>';

    if (String(cohort.instructions || '').trim()) {
      html += '<div class="prep"><h3>تعليمات مهمة</h3><p>' + esc(cohort.instructions) + '</p></div>';
    }
    if (String(cohort.bring || '').trim()) {
      html += '<div class="prep"><h3>ما تحتاج تجهّزه قبل البداية</h3><p>' + esc(cohort.bring) + '</p></div>';
    }
    if (!String(cohort.instructions || '').trim() && !String(cohort.bring || '').trim()) {
      html += '<div class="prep"><p class="muted tiny">تفاصيل التجهيز والتعليمات ستظهر هنا فور اعتمادها من فريق Fluent.</p></div>';
    }

    return html + '</section>';
  }

  /* ============================================================
     العرض
     ============================================================ */
  function overview(settings) {
    var d = app.data || {};
    var meta = FluentStore.statusMeta(FluentStore.APP_STATUS, app.status);

    var html =
      '<div class="hello">' +
        '<h1>مرحبًا، ' + esc(firstName(app.full_name)) + '</h1>' +
        '<p>هذه مساحتك في Fluent. تتابع منها حالة طلبك وتفاصيل تجربتك.</p>' +
      '</div>';

    if (app.status === 'accepted') html += acceptance(settings.cohort);

    html +=
      '<div class="portal-grid">' +
        '<section class="card">' +
          '<div class="card-h"><h2>طلب المحاكاة</h2><span class="sp"></span>' +
            '<span class="pill t-' + meta.tone + '">' + esc(meta.label) + '</span></div>' +
          '<dl class="dl">' +
            '<div><dt>رقم الطلب</dt><dd class="ltr">' + esc(app.reference) + '</dd></div>' +
            '<div><dt>التجربة / الدفعة</dt><dd>' + esc((settings.cohort || {}).name || 'يُعلن لاحقًا') + '</dd></div>' +
            '<div><dt>تاريخ التقديم</dt><dd>' + esc(fmtDate(app.created_at)) + '</dd></div>' +
            '<div><dt>حالة الطلب</dt><dd>' + esc(meta.label) + '</dd></div>' +
          '</dl>' +
          track(app.status) +
        '</section>' +

        '<aside class="card">' +
          '<div class="card-h"><h2>تحتاج مساعدة؟</h2></div>' +
          '<p class="tiny muted" style="line-height:1.85">أي سؤال عن طلبك أو عن التجربة، راسلنا وسنرد عليك.</p>' +
          '<p style="margin-top:1rem"><a href="mailto:' + esc((window.FLUENT_CONFIG || {}).contactEmail || 'info@fluent.sa') +
            '" dir="ltr" style="color:var(--signal);font-size:.9rem">' +
            esc((window.FLUENT_CONFIG || {}).contactEmail || 'info@fluent.sa') + '</a></p>' +
          '<p style="margin-top:1.4rem;padding-top:1.2rem;border-top:1px solid var(--line)">' +
            '<a href="/" class="tiny muted" style="display:inline-flex;align-items:center;gap:.4rem">العودة إلى موقع Fluent</a></p>' +
        '</aside>' +
      '</div>';

    return html;
  }

  function profile() {
    var d = app.data || {};
    var rows = [
      ['الاسم',            app.full_name, ''],
      ['البريد',           app.email, 'ltr'],
      ['الجوال',           app.phone, 'ltr'],
      ['المدينة',          d.city, ''],
      ['الجامعة',          d.university, ''],
      ['التخصص',           d.major, '']
    ];

    var html =
      '<div class="hello"><h1>الملف الشخصي</h1>' +
      '<p>البيانات التي أرسلتها مع طلبك. لتعديل أي منها راسل فريق Fluent.</p></div>' +
      '<section class="card" style="max-width:720px">';

    rows.forEach(function (r) {
      var v = String(r[1] || '').trim();
      html += '<div class="pf-row"><span class="pf-k">' + esc(r[0]) + '</span>' +
              '<span class="pf-v ' + r[2] + (v ? '' : ' muted') + '">' + esc(v || '—') + '</span></div>';
    });

    html += '<div class="pf-row"><span class="pf-k">السيرة الذاتية</span><span class="pf-v">';
    if (d.cv && d.cv.file_name) {
      html += '<span class="pf-file">' + ICON.doc + '<span dir="ltr">' + esc(d.cv.file_name) + '</span></span>';
    } else {
      html += '<span class="muted">—</span>';
    }
    html += '</span></div></section>';

    return html;
  }

  function render() {
    view.innerHTML = '<p class="muted tiny">جارٍ التحميل…</p>';
    Promise.all([
      FluentStore.getApplication(session.application_id),
      FluentStore.getSettings()
    ]).then(function (r) {
      app = r[0];
      var settings = r[1] || {};

      if (!app) {
        view.innerHTML = '<div class="empty-state"><b>ما لقينا طلبًا مرتبطًا بحسابك.</b>' +
          '<p class="tiny">إذا كنت قد سجّلت للتو، جرّب تسجيل الدخول مرة أخرى.</p>' +
          '<p style="margin-top:1.2rem"><a class="btn btn-primary" href="/apply">سجّل في المحاكاة' + ICON.arrow + '</a></p></div>';
        return;
      }

      who.textContent = firstName(app.full_name);
      view.innerHTML = current === 'profile' ? profile() : overview(settings);
    });
  }

  render();

  /* تحديث فوري إذا غيّرت الإدارة الحالة في تبويب آخر */
  window.addEventListener('storage', function (e) {
    if (e.key && e.key.indexOf('fluent.demo') === 0) render();
  });
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) render();
  });
})();
