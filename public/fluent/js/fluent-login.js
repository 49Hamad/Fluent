/* ============================================================
   FLUENT — Student sign-in («مساحتي في Fluent»)
   ------------------------------------------------------------
   Markup and steps from the approved prototype (assets/js/fluent-auth.js):
   e-mail → 6-digit code. Connected to Laravel (Phase 2):

     POST FLUENT_LOGIN.codeUrl   { email }        → neutral answer (always the same)
     POST FLUENT_LOGIN.verifyUrl { email, code }  → { redirect } or error

   No password, no localStorage, no demo accounts. The session is a normal
   secure Laravel session for the separate "student" guard.
   ============================================================ */

(function () {
  'use strict';

  var slot = document.getElementById('auth-slot');
  var CFG = window.FLUENT_LOGIN || {};
  if (!slot || !CFG.codeUrl) return;

  var ICON = {
    arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>',
    back:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    info:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4.5M12 16h.01"/></svg>'
  };
  var ERR = '<span class="ferr">' + ICON.info + '<span class="ferr-t"></span></span>';

  var state = { email: '', resendAt: 0, timer: null };

  function h(html) { var d = document.createElement('div'); d.innerHTML = html.trim(); return d.firstElementChild; }
  function esc(v) { var d = document.createElement('div'); d.textContent = String(v == null ? '' : v); return d.innerHTML; }

  function post(url, body) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CFG.csrf || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(body)
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (b) { return { status: r.status, body: b }; });
    });
  }

  function alertBox(title, text) {
    var old = slot.querySelector('.falert');
    if (old) old.remove();
    var box = h('<div class="falert" role="alert" style="margin-top:1.3rem;margin-bottom:0">' + ICON.info +
      '<span><b>' + esc(title) + '</b>' + (text ? ' ' + esc(text) : '') + '</span></div>');
    slot.appendChild(box);
  }

  function networkError() {
    alertBox('تعذّر الاتصال.', 'تحقّق من الاتصال وأعد المحاولة.');
  }

  /* ---------- Step 1: e-mail ---------- */
  function stepEmail() {
    clearInterval(state.timer);
    slot.innerHTML = '';
    var form = h(
      '<form novalidate>' +
        '<div class="ffield w-full">' +
          '<label class="flabel" for="a-email">البريد الإلكتروني <span class="req">*</span></label>' +
          '<input class="fctrl" id="a-email" type="email" dir="ltr" autocomplete="email" inputmode="email" placeholder="name@example.com">' +
          ERR +
        '</div>' +
        '<button class="btn btn-primary" type="submit"><span class="btn-label">أرسل رمز الدخول</span><span class="spin" aria-hidden="true"></span>' + ICON.arrow + '</button>' +
      '</form>'
    );
    slot.appendChild(form);

    var input = form.querySelector('#a-email');
    input.value = state.email;
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
      if (btn.dataset.loading) return;
      btn.dataset.loading = '1';

      post(CFG.codeUrl, { email: v }).then(function (res) {
        btn.removeAttribute('data-loading');
        if (res.status === 200) {
          state.email = v;
          state.resendAt = Date.now() + ((res.body.resend_after || 60) * 1000);
          stepCode(res.body.message);
          return;
        }
        if (res.status === 422 && res.body.errors) return fail(res.body.errors.email);
        if (res.status === 429) return alertBox('طلبات كثيرة.', res.body.message || 'حاول بعد قليل.');
        if (res.status === 419) return alertBox('انتهت صلاحية الصفحة.', 'حدّث الصفحة ثم أعد المحاولة.');
        networkError();
      }).catch(function () { btn.removeAttribute('data-loading'); networkError(); });
    });

    input.focus();
  }

  /* ---------- Step 2: code ---------- */
  function stepCode(message) {
    slot.innerHTML = '';
    var form = h(
      '<form novalidate>' +
        '<p class="fhelp" style="margin-bottom:1.3rem">' + esc(message ||
          'إذا كان هذا البريد مرتبطًا بطلب في Fluent، ستصلك رسالة فيها رمز الدخول خلال دقائق.') +
          '<br>البريد: <b dir="ltr" style="color:var(--paper)">' + esc(state.email) + '</b> · الرمز صالح لمدة 10 دقائق.</p>' +
        '<div class="ffield w-full">' +
          '<label class="flabel" for="a-code">رمز الدخول <span class="req">*</span></label>' +
          '<input class="fctrl" id="a-code" type="text" inputmode="numeric" autocomplete="one-time-code" dir="ltr" maxlength="6" placeholder="------" style="letter-spacing:.5em;text-align:center;font-size:1.1rem">' +
          ERR +
        '</div>' +
        '<button class="btn btn-primary" type="submit"><span class="btn-label">تأكيد الدخول</span><span class="spin" aria-hidden="true"></span>' + ICON.arrow + '</button>' +
        '<button class="btn btn-outline" type="button" id="a-resend" style="width:100%;margin-top:.7rem"><span class="btn-label">إعادة إرسال الرمز</span></button>' +
        '<button class="btn btn-outline" type="button" id="a-back" style="width:100%;margin-top:.7rem">' + ICON.back + 'تغيير البريد</button>' +
      '</form>'
    );
    slot.appendChild(form);

    var input  = form.querySelector('#a-code');
    var field  = form.querySelector('.ffield');
    var err    = form.querySelector('.ferr-t');
    var btn    = form.querySelector('button[type=submit]');
    var resend = form.querySelector('#a-resend');
    input.focus();

    form.querySelector('#a-back').addEventListener('click', stepEmail);
    input.addEventListener('input', function () {
      field.classList.remove('is-err');
      input.value = input.value.replace(/\D/g, '').slice(0, 6);
    });

    /* resend with a visible cooldown */
    function paintResend() {
      var left = Math.ceil((state.resendAt - Date.now()) / 1000);
      var lab = resend.querySelector('.btn-label');
      if (left > 0) { resend.disabled = true; lab.textContent = 'إعادة إرسال الرمز (' + left + ')'; }
      else { resend.disabled = false; lab.textContent = 'إعادة إرسال الرمز'; clearInterval(state.timer); }
    }
    clearInterval(state.timer);
    state.timer = setInterval(paintResend, 1000);
    paintResend();

    resend.addEventListener('click', function () {
      resend.disabled = true;
      post(CFG.codeUrl, { email: state.email }).then(function (res) {
        if (res.status === 200) {
          state.resendAt = Date.now() + ((res.body.resend_after || 60) * 1000);
          state.timer = setInterval(paintResend, 1000);
          paintResend();
          alertBox('تم.', 'إذا كان البريد مرتبطًا بطلب، وصلك رمز جديد. الرمز السابق لم يعد صالحًا.');
          return;
        }
        paintResend();
        if (res.status === 429) return alertBox('طلبات كثيرة.', res.body.message || 'حاول بعد قليل.');
        networkError();
      }).catch(function () { paintResend(); networkError(); });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (input.value.length !== 6) {
        field.classList.add('is-err');
        err.textContent = 'أدخل الرمز المكوّن من ٦ أرقام.';
        return;
      }
      if (btn.dataset.loading) return;
      btn.dataset.loading = '1';
      post(CFG.verifyUrl, { email: state.email, code: input.value }).then(function (res) {
        if (res.status === 200 && res.body.redirect) { location.href = res.body.redirect; return; }
        btn.removeAttribute('data-loading');
        if (res.status === 422 && res.body.errors) {
          field.classList.add('is-err');
          err.textContent = res.body.errors.code;
          input.select();
          return;
        }
        if (res.status === 429) return alertBox('محاولات كثيرة.', res.body.message || 'حاول بعد قليل.');
        if (res.status === 419) return alertBox('انتهت صلاحية الصفحة.', 'حدّث الصفحة ثم أعد المحاولة.');
        networkError();
      }).catch(function () { btn.removeAttribute('data-loading'); networkError(); });
    });
  }

  stepEmail();
})();
