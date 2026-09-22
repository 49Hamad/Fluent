/* ============================================================
   FLUENT — Student login UI (frontend review mode)
   ------------------------------------------------------------
   Markup, steps and messages are taken from the approved prototype
   (assets/js/fluent-auth.js): email → 6-digit code.

   What is deliberately NOT migrated:
     - the prototype's demo authentication (any code accepted,
       session kept in localStorage) — insecure, never for production
     - any connection to employee / Filament authentication

   In this preview nothing is sent and nobody is signed in.
   Confirming the code shows a subtle development-only notice.
   The real student authentication is built in a later phase.
   ============================================================ */

(function () {
  'use strict';

  var slot = document.getElementById('auth-slot');
  if (!slot) return;

  var PREVIEW = window.FLUENT_PREVIEW || {};

  var ICON = {
    arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>',
    back:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    spark: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/></svg>',
    info:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4.5M12 16h.01"/></svg>'
  };
  var ERR = '<span class="ferr">' + ICON.info + '<span class="ferr-t"></span></span>';

  var state = { email: '' };

  function h(html) { var d = document.createElement('div'); d.innerHTML = html.trim(); return d.firstElementChild; }
  function esc(v) { var d = document.createElement('div'); d.textContent = String(v == null ? '' : v); return d.innerHTML; }

  /* Subtle development-only notice (prototype's .fpreview style) */
  function devNotice(text) {
    var old = slot.querySelector('.fpreview[data-dev]');
    if (old) old.remove();
    var box = h('<div class="fpreview" data-dev role="status" style="margin-top:1.3rem;margin-bottom:0">' + ICON.info +
      '<span>' + text + '</span></div>');
    slot.appendChild(box);
  }

  /* ---------- Step 1: email ---------- */
  function stepEmail() {
    slot.innerHTML = '';
    var form = h(
      '<form novalidate>' +
        '<div class="ffield w-full">' +
          '<label class="flabel" for="a-email">البريد الإلكتروني <span class="req">*</span></label>' +
          '<input class="fctrl" id="a-email" type="email" dir="ltr" autocomplete="email" placeholder="name@example.com" value="' + esc(state.email) + '">' +
          ERR +
        '</div>' +
        '<button class="btn btn-primary" type="submit"><span class="btn-label">أرسل رمز الدخول</span><span class="spin" aria-hidden="true"></span>' + ICON.arrow + '</button>' +
      '</form>'
    );
    slot.appendChild(form);

    var input = form.querySelector('#a-email');
    var field = form.querySelector('.ffield');
    var err   = form.querySelector('.ferr-t');
    var btn   = form.querySelector('button');

    function fail(msg) { field.classList.add('is-err'); err.textContent = msg; input.focus(); }
    input.addEventListener('input', function () { field.classList.remove('is-err'); });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var v = input.value.trim();
      if (!v) return fail('الرجاء إدخال البريد الإلكتروني.');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return fail('تأكد من صيغة البريد الإلكتروني.');
      btn.dataset.loading = '1';
      state.email = v;
      /* Review mode: no email is sent. */
      setTimeout(stepCode, 600);
    });

    previewBox();
  }

  /* ---------- Step 2: code ---------- */
  function stepCode() {
    slot.innerHTML = '';
    var form = h(
      '<form novalidate>' +
        '<p class="fhelp" style="margin-bottom:1.3rem">أرسلنا رمزًا مكوّنًا من ٦ أرقام إلى <b dir="ltr" style="color:var(--paper)">' + esc(state.email) + '</b></p>' +
        '<div class="ffield w-full">' +
          '<label class="flabel" for="a-code">رمز الدخول <span class="req">*</span></label>' +
          '<input class="fctrl" id="a-code" type="text" inputmode="numeric" autocomplete="one-time-code" dir="ltr" maxlength="6" placeholder="------" style="letter-spacing:.5em;text-align:center;font-size:1.1rem">' +
          ERR +
        '</div>' +
        '<button class="btn btn-primary" type="submit"><span class="btn-label">تأكيد الدخول</span><span class="spin" aria-hidden="true"></span>' + ICON.arrow + '</button>' +
        '<button class="btn btn-outline" type="button" id="a-back" style="width:100%;margin-top:.7rem">' + ICON.back + 'تغيير البريد</button>' +
      '</form>'
    );
    slot.appendChild(form);

    var input = form.querySelector('#a-code');
    var field = form.querySelector('.ffield');
    var err   = form.querySelector('.ferr-t');
    var btn   = form.querySelector('button[type=submit]');
    input.focus();

    form.querySelector('#a-back').addEventListener('click', stepEmail);
    input.addEventListener('input', function () {
      field.classList.remove('is-err');
      input.value = input.value.replace(/\D/g, '').slice(0, 6);
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (input.value.length !== 6) {
        field.classList.add('is-err');
        err.textContent = 'أدخل الرمز المكوّن من ٦ أرقام.';
        return;
      }
      btn.dataset.loading = '1';
      setTimeout(function () {
        btn.removeAttribute('data-loading');
        devNotice('<b>تسجيل الدخول غير مربوط في هذه المعاينة.</b> لم يُرسَل أي رمز ولم يُنشأ أي دخول. ' +
          (PREVIEW.portalUrl ? 'لمعاينة مساحة الطالب استخدم الصندوق أدناه.' : ''));
      }, 500);
    });

    previewBox();
  }

  /* ---------- Development-only portal preview box ----------
     Replaces the prototype's "demo accounts" box. It opens the
     portal preview with safe mock data; it signs nobody in.
     Rendered only when the preview route is enabled. */
  function previewBox() {
    if (!PREVIEW.portalUrl) return;
    var states = PREVIEW.states || [];
    var box = h(
      '<div class="demobox">' +
        '<span class="demobox-h">' + ICON.spark + 'معاينة مساحة الطالب — لبيئة التطوير فقط</span>' +
        '<p>بيانات تجريبية آمنة لمراجعة التصميم. لا تسجيل دخول ولا بيانات حقيقية، ولا يظهر هذا الصندوق في النسخة الحقيقية.</p>' +
        '<div class="demolist"></div>' +
      '</div>'
    );
    var list = box.querySelector('.demolist');
    states.forEach(function (s) {
      var b = h('<button type="button"><span class="dn"></span><span class="ds"></span></button>');
      b.querySelector('.dn').textContent = s.example;
      b.querySelector('.ds').textContent = s.label;
      b.addEventListener('click', function () {
        location.href = PREVIEW.portalUrl + '?state=' + encodeURIComponent(s.id);
      });
      list.appendChild(b);
    });
    slot.appendChild(box);
  }

  stepEmail();
})();
