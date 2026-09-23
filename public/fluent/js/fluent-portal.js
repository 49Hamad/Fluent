/* ============================================================
   From the approved prototype (assets/js/fluent-portal.js). Changes (Phase 2):
     - page links (*.html) → Laravel routes
     - the 7 real statuses (مقبول مبدئيًا / مقبول نهائيًا instead of «مقبول»)
     - interview details shown to the student when invited to an interview
     - text is HTML-escaped before display (the prototype did not escape;
       required now that the data is real)
     - several applications (future): a small switcher; one → opens directly
     - seat-confirmation steps for «مقبول مبدئيًا»: agreement → optional media
       consent → bank transfer → receipt upload → verification → seat
       (all rules are enforced on the server; this file only displays them)
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

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

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
    var terminal = ['preliminary_accepted', 'final_accepted', 'rejected', 'waitlist'].indexOf(status) > -1;
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
    if (status === 'final_accepted') {
      return '<div class="outcome t-good"><b>مقبول نهائيًا</b>' +
        'تفاصيل التجربة في الأعلى. نراك قريبًا.</div>';
    }
    if (status === 'preliminary_accepted') {
      return '<div class="outcome t-good"><b>مقبول مبدئيًا</b>' +
        'يسعدنا إبلاغك بقبولك مبدئيًا في تجربة Fluent. أكمل «خطوات تأكيد مقعدك» في الأعلى — مقعدك يتأكد بعد التحقق من السداد.</div>';
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
      var iv = (app && app.interview) || {};
      var rows = [];
      if (iv.at)       rows.push(['الموعد', iv.at, '']);   /* already formatted by the server */
      if (iv.mode)     rows.push(['نوع المقابلة', iv.mode, '']);
      if (iv.location) rows.push(['المكان / الرابط', iv.location, /^https?:\/\//i.test(iv.location) ? 'link' : '']);
      if (!rows.length && !iv.note) {
        return '<div class="outcome"><b>مرشح للمقابلة</b>' +
          'وصلك ترشيح لمقابلة قصيرة. سنراسلك على بريدك لتحديد الموعد.</div>';
      }
      var h = '<div class="outcome"><b>مرشح للمقابلة</b>تفاصيل مقابلتك:' +
        '<dl class="dl" style="margin-top:.9rem">';
      rows.forEach(function (r) {
        var v = r[2] === 'link'
          ? '<a href="' + esc(r[1]) + '" target="_blank" rel="noopener" dir="ltr" style="color:var(--signal)">' + esc(r[1]) + '</a>'
          : esc(r[1]);
        h += '<div><dt>' + esc(r[0]) + '</dt><dd>' + v + '</dd></div>';
      });
      h += '</dl>';
      if (iv.note) h += '<p style="margin-top:.8rem">' + esc(iv.note) + '</p>';
      return h + '</div>';
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
     خطوات تأكيد المقعد (بعد القبول المبدئي)
     ============================================================ */
  var ENR_ICON = {
    check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>',
    lock:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>',
    info:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4.5M12 16h.01"/></svg>'
  };

  /* ما المطلوب من الطالب الآن — جملة واحدة واضحة */
  function nextStep(e, final) {
    var p = e.payment;
    if (final || e.seat.confirmed) return ['good', 'مقعدك مؤكد', 'اكتملت كل الخطوات. تفاصيل دفعتك في الأعلى.'];
    if (!e.ready && !e.agreement.accepted) return ['neutral', 'نجهّز لك الخطوة التالية', 'الاتفاقية وتفاصيل الرسوم لدفعتك قيد الإعداد. سنبلغك على بريدك فور جاهزيتها.'];
    if (!e.agreement.accepted) return ['signal', 'المطلوب منك الآن: اقرأ الاتفاقية ووافق عليها', 'بعد الموافقة تظهر لك تفاصيل التحويل البنكي والمبلغ المطلوب.'];
    if (p.status === 'reupload_requested') return ['hold', 'المطلوب منك الآن: ارفع إيصالًا جديدًا', 'راجع الملاحظة في خطوة رفع الإيصال.'];
    if (p.status === 'awaiting_transfer') return ['signal', 'المطلوب منك الآن: حوّل الرسوم ثم ارفع الإيصال', 'تفاصيل الحساب والمبلغ في الخطوة ٤.'];
    if (p.status === 'verified') return ['good', 'تم التحقق من السداد', 'بقي تأكيد مقعدك من فريق Fluent. سيصلك بريد عند التأكيد.'];
    return ['neutral', 'لا شيء مطلوب منك الآن', 'وصلنا إيصالك ونتحقق من التحويل. سنحدّث حالتك هنا.'];
  }

  function stepHtml(n, state, title, body) {
    /* state: done | on | locked | wait */
    var mark = state === 'done' ? ENR_ICON.check : (state === 'locked' ? ENR_ICON.lock : String(n));
    return '<li class="estep is-' + state + '"><span class="estep-n" aria-hidden="true">' + mark + '</span>' +
      '<div class="estep-b"><h3>' + esc(title) + '</h3>' + body + '</div></li>';
  }

  function enrollmentSection(e, final) {
    var a = e.agreement, p = e.payment, m = e.media, seat = e.seat;
    var ns = nextStep(e, final);
    var html = '<section class="card enroll" id="enroll">' +
      '<div class="card-h"><h2>خطوات تأكيد مقعدك</h2><span class="sp"></span>' +
        '<span class="pill t-' + (seat.confirmed || final ? 'good' : 'signal') + '">' + (seat.confirmed || final ? 'مقعدك مؤكد' : 'بانتظار إكمال الخطوات') + '</span></div>' +
      '<div class="enext t-' + ns[0] + '"><b>' + esc(ns[1]) + '</b><span>' + esc(ns[2]) + '</span></div>' +
      '<ol class="esteps">';

    /* 1 — القبول المبدئي */
    html += stepHtml(1, 'done', 'القبول المبدئي', '<p>تم قبولك مبدئيًا في الدفعة.</p>');

    /* 2 — الاتفاقية */
    var agBody;
    if (a.accepted) {
      agBody = '<p>وافقت على «' + esc(a.title) + '» (النسخة ' + esc(a.version) + ') في ' + esc(a.accepted_at) + '.</p>' +
        '<p><button type="button" class="elink" data-go="agreement">عرض الاتفاقية التي وافقت عليها</button></p>';
    } else if (!e.ready) {
      agBody = '<p class="muted">الاتفاقية قيد الإعداد لدفعتك.</p>';
    } else {
      agBody = '<p>اقرأ اتفاقية المشاركة كاملة، وفيها بيانات الدفعة والرسوم وسياسة الانسحاب والاسترداد.</p>' +
        '<p><button type="button" class="btn btn-primary btn-sm" data-go="agreement">اقرأ الاتفاقية ووافق عليها' + ICON.arrow + '</button></p>';
    }
    html += stepHtml(2, a.accepted ? 'done' : (e.ready ? 'on' : 'wait'), 'اتفاقية المشاركة', agBody);

    /* 3 — الموافقة الإعلامية (اختيارية ومنفصلة) */
    var cur = m.decided ? (m.granted ? 'yes' : 'no') : '';
    var mBody = '<p class="tiny muted">اختيارية ولا تؤثر على قبولك أو مقعدك، وتقدر تغيّرها لاحقًا.</p>' +
      '<form class="emedia" novalidate><p class="emedia-t">' + esc(m.text) + '</p>' +
      '<div class="fopts is-inline">' +
        '<label class="fopt"><input type="radio" name="granted" value="yes"' + (cur === 'yes' ? ' checked' : '') + '><span class="fbox is-radio" aria-hidden="true"></span><span>أوافق</span></label>' +
        '<label class="fopt"><input type="radio" name="granted" value="no"' + (cur === 'no' ? ' checked' : '') + '><span class="fbox is-radio" aria-hidden="true"></span><span>لا أوافق</span></label>' +
      '</div>' +
      '<div class="erow"><button type="submit" class="btn btn-outline btn-sm">حفظ اختياري</button>' +
      (m.decided ? '<span class="tiny muted">اختيارك الحالي: ' + (m.granted ? 'موافق' : 'غير موافق') + ' · ' + esc(m.decided_at) + '</span>' : '<span class="tiny muted">لم تختر بعد.</span>') +
      '</div><div class="emsg" role="status"></div></form>';
    html += stepHtml(3, m.decided ? 'done' : 'on', 'الموافقة على التصوير والنشر (اختيارية)', mBody);

    /* 4 — التحويل البنكي */
    var bBody;
    if (!a.accepted) {
      bBody = '<p class="muted">تظهر تفاصيل التحويل والمبلغ المطلوب بعد الموافقة على الاتفاقية.</p>';
    } else if (p.bank) {
      var b = p.bank;
      bBody = '<div class="eamount"><span>المبلغ المطلوب</span><b>' + esc(p.amount || '—') + '</b></div>' +
        '<dl class="dl ebank">' +
          (b.bank_name ? '<div><dt>البنك</dt><dd>' + esc(b.bank_name) + '</dd></div>' : '') +
          (b.beneficiary_name ? '<div><dt>اسم المستفيد</dt><dd>' + esc(b.beneficiary_name) + '</dd></div>' : '') +
          (b.iban ? '<div><dt>رقم الآيبان (IBAN)</dt><dd class="ltr">' + esc(b.iban) + '</dd></div>' : '') +
          (b.account_number ? '<div><dt>رقم الحساب</dt><dd class="ltr">' + esc(b.account_number) + '</dd></div>' : '') +
          (p.deadline ? '<div><dt>آخر موعد للسداد</dt><dd>' + esc(p.deadline) + '</dd></div>' : '') +
          '<div><dt>مرجع التحويل</dt><dd class="ltr">' + esc(app.reference) + '</dd></div>' +
        '</dl>' +
        (b.instructions ? '<p class="einstr">' + esc(b.instructions) + '</p>' : '') +
        '<p class="tiny muted">التحويل يتم من تطبيق أو موقع بنكك مباشرة. Fluent لا تطلب منك بيانات بطاقتك أو حسابك.</p>';
    } else {
      bBody = '<p class="muted">تفاصيل التحويل ستظهر هنا قريبًا.</p>';
    }
    var paid = ['receipt_uploaded', 'under_review', 'verified'].indexOf(p.status) > -1;
    html += stepHtml(4, !a.accepted ? 'locked' : (paid ? 'done' : 'on'), 'التحويل البنكي', bBody);

    /* 5 — رفع الإيصال */
    var rBody = '';
    if (!a.accepted) {
      rBody = '<p class="muted">بعد التحويل ترفع إيصال التحويل هنا.</p>';
    } else {
      rBody += '<p>حالة السداد: <span class="pill t-' + payTone(p.status) + '">' + esc(p.label) + '</span></p>';
      if (p.reupload_reason) {
        rBody += '<div class="falert" role="alert">' + ENR_ICON.info + '<span><b>نحتاج إيصالًا جديدًا.</b> ' + esc(p.reupload_reason) + '</span></div>';
      }
      if (p.can_upload) {
        rBody += '<form class="ereceipt" novalidate>' +
          '<label class="flabel" for="e-receipt">ملف الإيصال</label>' +
          '<input class="fctrl" id="e-receipt" name="receipt" type="file" accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png">' +
          '<p class="fhelp">PDF أو JPG أو PNG · الحد الأقصى ' + esc(p.max_mb) + ' ميجابايت. رفع الإيصال لا يعني تأكيد السداد — نتحقق منه أولًا.</p>' +
          '<div class="erow"><button type="submit" class="btn btn-primary btn-sm"><span class="btn-label">رفع الإيصال</span></button></div>' +
          '<div class="emsg" role="status"></div></form>';
      } else if (p.status === 'receipt_uploaded' || p.status === 'under_review') {
        rBody += '<p class="tiny muted">وصلنا إيصالك. سنتحقق من التحويل ونحدّث حالتك هنا.</p>';
      }
      if (p.receipts && p.receipts.length) {
        var U = FluentStore.urls();
        rBody += '<ul class="ereceipts">';
        p.receipts.forEach(function (r) {
          rBody += '<li><a href="' + esc(U.receiptDownloadUrl + '/' + encodeURIComponent(r.id)) + '">' + ICON.doc +
            '<span dir="ltr">' + esc(r.name) + '</span></a><span class="tiny muted">' + esc(r.uploaded_at) + ' · ' + esc(r.review) + '</span></li>';
        });
        rBody += '</ul>';
      }
    }
    html += stepHtml(5, !a.accepted ? 'locked' : (p.status === 'verified' ? 'done' : (p.can_upload ? 'on' : 'wait')), 'رفع إيصال التحويل', rBody);

    /* 6 — تأكيد المقعد */
    var sBody = (seat.confirmed || final)
      ? '<p>تم تأكيد مقعدك' + (seat.confirmed_at ? ' في ' + esc(seat.confirmed_at) : '') + '. أنت الآن مقبول نهائيًا.</p>'
      : '<p class="muted">بعد التحقق من السداد يؤكد فريق Fluent مقعدك، وتصبح مقبولًا نهائيًا.</p>';
    html += stepHtml(6, (seat.confirmed || final) ? 'done' : (p.status === 'verified' ? 'wait' : 'locked'), 'تأكيد المقعد', sBody);

    return html + '</ol></section>';
  }

  function payTone(st) {
    return st === 'verified' ? 'good' : (st === 'reupload_requested' ? 'hold' : (st === 'awaiting_transfer' ? 'neutral' : 'signal'));
  }

  /* ---- صفحة الاتفاقية ---- */
  function agreementView() {
    var e = app.enrollment;
    if (!e || !e.agreement || (!e.agreement.accepted && !e.ready)) {
      return '<div class="hello"><h1>اتفاقية المشاركة</h1><p>الاتفاقية قيد الإعداد لدفعتك.</p></div>';
    }
    var a = e.agreement, d = a.details || {};
    var rows = [
      ['الطرف الأول', d.party], ['المشارك', d.participant], ['رقم الطلب', d.reference],
      ['الدفعة', d.cohort], ['تاريخ البداية', d.start_date], ['الوقت', d.schedule], ['الموقع', d.location],
      ['الرسوم', d.fee], ['طريقة السداد', d.payment_method], ['آخر موعد للسداد', d.payment_deadline]
    ];
    var html = '<div class="hello"><h1>' + esc(a.title) + '</h1><p>النسخة ' + esc(a.version) +
      (a.accepted ? ' · وافقت عليها في ' + esc(a.accepted_at) : ' · اقرأها كاملة قبل الموافقة') + '</p></div>' +
      '<section class="card agree">' +
        '<h2 class="agree-h">بيانات الدفعة والرسوم</h2><dl class="dl">';
    rows.forEach(function (r) {
      var v = String(r[1] || '').trim();
      html += '<div><dt>' + esc(r[0]) + '</dt><dd' + (v ? '' : ' class="tbd"') + '>' + esc(v || 'يُعلن لاحقًا') + '</dd></div>';
    });
    /* a.html is rendered on the server from Markdown with raw HTML escaped */
    html += '</dl><div class="agree-body">' + a.html + '</div>';

    if (a.accepted) {
      html += '<div class="enext t-good"><b>تمت الموافقة</b><span>' + esc(a.statement) + '</span></div>';
    } else if (app.status === 'preliminary_accepted') {
      html += '<form class="eagree" novalidate>' +
        '<label class="fopt"><input type="checkbox" name="confirm" id="e-confirm"><span class="fbox is-check" aria-hidden="true"></span><span>' + esc(a.statement) + '</span></label>' +
        '<div class="erow"><button type="submit" class="btn btn-primary" disabled><span class="btn-label">أوافق على الاتفاقية</span>' + ICON.arrow + '</button>' +
        '<button type="button" class="btn btn-outline" data-go="overview">رجوع</button></div>' +
        '<div class="emsg" role="status"></div></form>';
    }
    html += '</section>';
    if (a.accepted) html += '<p style="margin-top:1.2rem"><button type="button" class="btn btn-outline" data-go="overview">رجوع إلى خطوات تأكيد المقعد</button></p>';
    return html;
  }

  /* ---- ربط النماذج بالخادم ---- */
  function say(form, text, ok) {
    var m = form.querySelector('.emsg');
    if (m) { m.textContent = text || ''; m.className = 'emsg ' + (ok ? 'is-ok' : 'is-err'); }
  }
  function failText(res) {
    if (res.status === 429) return 'محاولات كثيرة. انتظر دقيقة ثم أعد المحاولة.';
    if (res.status === 419) return 'انتهت صلاحية الصفحة. حدّث الصفحة ثم أعد المحاولة.';
    return (res.body && res.body.message) || 'تعذّر الحفظ. تحقّق من الاتصال وأعد المحاولة.';
  }

  function bindEnrollment() {
    var U = FluentStore.urls();
    Array.prototype.forEach.call(view.querySelectorAll('[data-go]'), function (b) {
      b.addEventListener('click', function () { current = b.dataset.go; render(); window.scrollTo(0, 0); });
    });

    var ag = view.querySelector('form.eagree');
    if (ag) {
      var cb = ag.querySelector('#e-confirm'), btn = ag.querySelector('button[type=submit]');
      cb.addEventListener('change', function () { btn.disabled = !cb.checked; });
      ag.addEventListener('submit', function (ev) {
        ev.preventDefault();
        if (!cb.checked) { say(ag, 'لا بد من الإقرار بقراءة الاتفاقية والموافقة عليها للمتابعة.'); return; }
        btn.disabled = true;
        var a = app.enrollment.agreement;
        FluentStore.post(U.agreementUrl, { agreement_id: a.id, hash: a.hash, confirm: 1 }, app.reference).then(function (res) {
          if (res.status === 200) { current = 'overview'; render(); window.scrollTo(0, 0); return; }
          btn.disabled = false; say(ag, failText(res));
          if (res.body && res.body.code === 'agreement_changed') setTimeout(render, 2500);
        }).catch(function () { btn.disabled = false; say(ag, 'تعذّر الاتصال. أعد المحاولة.'); });
      });
    }

    var md = view.querySelector('form.emedia');
    if (md) {
      md.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var c = md.querySelector('input[name=granted]:checked');
        if (!c) { say(md, 'اختر «أوافق» أو «لا أوافق».'); return; }
        FluentStore.post(U.mediaUrl, { granted: c.value }, app.reference).then(function (res) {
          if (res.status === 200) { render(); return; }
          say(md, failText(res));
        }).catch(function () { say(md, 'تعذّر الاتصال. أعد المحاولة.'); });
      });
    }

    var rc = view.querySelector('form.ereceipt');
    if (rc) {
      rc.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var input = rc.querySelector('input[type=file]'), f = input.files && input.files[0];
        var max = (app.enrollment.payment.max_mb || 5) * 1024 * 1024;
        if (!f) { say(rc, 'اختر ملف الإيصال.'); return; }
        if (!/\.(pdf|jpe?g|png)$/i.test(f.name)) { say(rc, 'الصيغ المقبولة: PDF أو JPG أو PNG.'); return; }
        if (f.size > max) { say(rc, 'حجم الملف أكبر من الحد المسموح (5 ميجابايت).'); return; }
        var btn = rc.querySelector('button[type=submit]'), lab = btn.querySelector('.btn-label');
        btn.disabled = true; lab.textContent = 'جارٍ رفع الملف…';
        var fd = new FormData(); fd.append('receipt', f, f.name);
        FluentStore.post(U.receiptUrl, fd, app.reference).then(function (res) {
          if (res.status === 200) { render(); return; }
          btn.disabled = false; lab.textContent = 'رفع الإيصال'; say(rc, failText(res));
        }).catch(function () { btn.disabled = false; lab.textContent = 'رفع الإيصال'; say(rc, 'تعذّر الاتصال. أعد المحاولة.'); });
      });
    }
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

    var list = (FluentStore.applications && FluentStore.applications()) || [];
    if (list.length > 1) {
      /* Several applications (future cohorts): open the relevant one. */
      html += '<ul class="chips" style="margin:-.6rem 0 1.6rem" aria-label="تجاربي في Fluent">';
      list.forEach(function (x) {
        var on = x.reference === app.reference;
        html += '<li><a class="chip" href="?application=' + encodeURIComponent(x.reference) + '"' +
          (on ? ' aria-current="true" style="border-color:var(--signal);color:var(--paper)"' : '') + '>' +
          esc(x.cohort || x.reference) + '</a></li>';
      });
      html += '</ul>';
    }

    if (app.status === 'final_accepted') html += acceptance(settings.cohort);
    if (app.enrollment && app.status === 'preliminary_accepted') html += enrollmentSection(app.enrollment, false);

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

    /* After final acceptance: the completed steps stay visible (agreement, media choice, receipts) */
    if (app.enrollment && app.status === 'final_accepted') html += enrollmentSection(app.enrollment, true);

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
      if (current === 'agreement' && !app.enrollment) current = 'overview';
      view.innerHTML = current === 'profile' ? profile() : (current === 'agreement' ? agreementView() : overview(settings));
      Array.prototype.forEach.call(nav.children, function (n) {
        n.classList.toggle('is-on', n.dataset.view === (current === 'agreement' ? 'overview' : current));
      });
      bindEnrollment();
    });
  }

  render();

  /* تحديث فوري إذا غيّرت الإدارة الحالة في تبويب آخر */
  window.addEventListener('storage', function (e) {
    if (e.key && e.key.indexOf('fluent.demo') === 0) render();
  });
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) return;
    var f = view.querySelector('form.ereceipt input[type=file]');
    if (f && f.files && f.files.length) return;          /* don't drop a file the student just picked */
    if (view.querySelector('#e-confirm:checked')) return;
    render();
  });
})();
