/* ============================================================
   FLUENT — محرّك النماذج
   ------------------------------------------------------------
   JavaScript خالص، بلا مكتبات وبلا CDN وبلا عملية بناء.
   يبني النموذج من fluent-schemas.js ويرسله إلى Supabase
   عبر REST API مباشرة (fetch).

   نادرًا ما تحتاج تعديل هذا الملف.
   لتغيير الأسئلة → assets/js/fluent-schemas.js
   لتغيير حالة التسجيل أو مفاتيح Supabase → assets/js/fluent-config.js
   ============================================================ */

(function (window, document) {
  'use strict';

  var CFG = window.FLUENT_CONFIG || {};
  var SCHEMAS = window.FLUENT_SCHEMAS || {};

  /* ---------- أدوات صغيرة ---------- */
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function uid(p) { return p + '-' + Math.random().toString(36).slice(2, 8); }
  function esc(s) { return String(s == null ? '' : s); }

  var ICON = {
    err: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4.5M12 16h.01"/></svg>',
    arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>',
    arrowBack: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    upload: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M8 8l4-4 4 4"/><path d="M4 16v2.5A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5V16"/></svg>',
    doc: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a1.5 1.5 0 0 0-1.5 1.5v15A1.5 1.5 0 0 0 7 21h10a1.5 1.5 0 0 0 1.5-1.5V7.5z"/><path d="M14 3v4.5h4.5"/></svg>',
    close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>'
  };

  /* ---------- رسائل التحقّق ---------- */
  var MSG = {
    required:  'هذا الحقل مطلوب.',
    consent:   'لا بد من الموافقة للمتابعة.',
    email:     'تأكد من صيغة البريد الإلكتروني.',
    tel:       'أدخل رقم جوال صحيح (مثال: 0512345678).',
    url:       'أدخل رابطًا صحيحًا يبدأ بـ https://',
    number:    'أدخل رقمًا صحيحًا.',
    min:       function (n) { return 'أقل قيمة مسموحة ' + n + '.'; },
    max:       function (n) { return 'أعلى قيمة مسموحة ' + n + '.'; },
    minlength: function (n) { return 'اكتب ' + n + ' حرفًا على الأقل.'; },
    pick:      'اختر أحد الخيارات.',
    fileReq:   'الرجاء إرفاق الملف.',
    filePdf:   'الملف يجب أن يكون بصيغة PDF.',
    fileSize:  function (mb) { return 'حجم الملف أكبر من الحد المسموح (' + mb + ' ميجابايت).'; },
    fileEmpty: 'الملف فارغ. تأكد من اختيار الملف الصحيح.'
  };

  /* تحويل الحجم إلى نص عربي مقروء */
  function humanSize(bytes) {
    if (bytes < 1024) return bytes + ' بايت';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' كيلوبايت';
    return (bytes / 1024 / 1024).toFixed(1) + ' ميجابايت';
  }

  var RE = {
    email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
    tel:   /^\+?\d{8,15}$/,
    url:   /^https?:\/\/[^\s.]+\.[^\s]{2,}$/i
  };

  /* ============================================================
     حالة التسجيل — أي نموذج نعرضه؟
     ============================================================ */
  function resolveJourney(journey) {
    var reg = (CFG.registration || {})[journey] || 'open';

    /* إن كانت طبقة البيانات موجودة، فحالة التسجيل التي يضبطها المشرف
       من لوحة الإدارة هي المرجع. عند الإنتاج تأتي من Supabase. */
    if (journey === 'student' && window.FluentStore && FluentStore.getSettingsSync) {
      var st = FluentStore.getSettingsSync();
      if (st && st.registration) reg = st.registration;
    }
    var copy = (CFG.registrationCopy || {})[reg] || {};
    var schema = null;

    if (reg === 'open') schema = SCHEMAS[journey];
    else if (reg === 'waitlist') schema = SCHEMAS[journey + 'Waitlist'] || null;

    /* حالة الإغلاق: نموذج التنبيه يُعرض بعد ضغط الزر، لا مباشرة */
    var notify = SCHEMAS[journey + 'Notify'] || null;

    return { state: reg, copy: copy, schema: schema, notify: notify };
  }

  /* ============================================================
     الإرسال إلى Supabase
     ============================================================ */
  /* المفتاح العام: ندعم التسمية الجديدة (publishableKey) والقديمة (anonKey) */
  function publicKey() {
    var s = CFG.supabase || {};
    return s.publishableKey || s.anonKey || '';
  }

  function isConfigured() {
    var s = CFG.supabase || {};
    return !!(s.url && publicKey());
  }

  /* هل نحن في وضع المعاينة؟ لا يحتاج Supabase إطلاقًا. */
  function isPreview() {
    return CFG.previewMode === true || !isConfigured();
  }

  function makeReference() {
    var d = new Date();
    var p = function (n) { return ('0' + n).slice(-2); };
    var rand = Math.random().toString(36).slice(2, 6).toUpperCase();
    return 'FL-' + String(d.getFullYear()).slice(2) + p(d.getMonth() + 1) + p(d.getDate()) + '-' + rand;
  }

  function send(schema, row) {
    var table = (CFG.tables || {})[schema.table] || schema.table;

    /* ---------- وضع المعاينة ----------
       محاكاة إرسال ناجح محليًا. لا شبكة، لا قاعدة بيانات، لا مفاتيح.
       التفاصيل تظهر في Console للمطوّر فقط — الزائر لا يرى أي لغة تقنية. */
    if (CFG.previewMode === true) {
      console.info(
        '%c[Fluent · وضع المعاينة]%c إرسال محاكى — لم تُخزَّن أي بيانات.',
        'background:#f7c800;color:#0f0f0f;padding:2px 6px;border-radius:3px;font-weight:600',
        'color:inherit'
      );
      console.info('[Fluent · معاينة] الجدول المستهدف:', table);
      console.info('[Fluent · معاينة] الحمولة التي كانت ستُرسَل:', row);
      return new Promise(function (res) { setTimeout(res, 850); });
    }

    if (!isConfigured()) return Promise.reject(new Error('CONFIG'));

    var base = String(CFG.supabase.url).replace(/\/+$/, '');
    var key = publicKey();
    var ep = CFG.endpoint || { mode: 'rest' };
    var url, body;

    if (ep.mode === 'function') {
      /* مسار الدالة الطرفية — يُستخدم لاحقًا مع Turnstile
         (الدالة تتحقّق من الرمز ثم تُدخل الصف بصلاحية الخادم) */
      url = base + '/functions/v1/' + encodeURIComponent(ep.name || 'submit-form');
      body = JSON.stringify({ table: table, row: row });
    } else {
      url = base + '/rest/v1/' + encodeURIComponent(table);
      body = JSON.stringify(row);
    }

    return fetch(url, {
      method: 'POST',
      headers: {
        'apikey': key,
        'Authorization': 'Bearer ' + key,
        'Content-Type': 'application/json',
        'Prefer': 'return=minimal'
      },
      body: body
    }).then(function (r) {
      if (r.ok) return true;
      return r.text().then(function (t) {
        throw new Error('HTTP ' + r.status + ' — ' + t.slice(0, 300));
      });
    });
  }

  /* ============================================================
     رفع الملفات إلى Supabase Storage (سلال خاصة)
     ------------------------------------------------------------
     الملف نفسه لا يدخل قاعدة البيانات ولا يُحوَّل إلى base64.
     يُرفع إلى سلة خاصة، ولا يُحفظ في الصف إلا مسار التخزين.
     المسار عشوائي غير قابل للتخمين (16 بايت عشوائية).
     ============================================================ */
  function randomId() {
    var bytes = new Uint8Array(16);
    if (window.crypto && window.crypto.getRandomValues) window.crypto.getRandomValues(bytes);
    else for (var i = 0; i < 16; i++) bytes[i] = Math.floor(Math.random() * 256);
    return Array.prototype.map.call(bytes, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
  }

  function storagePath(file) {
    var d = new Date();
    var p = function (n) { return ('0' + n).slice(-2); };
    /* اسم الملف الأصلي لا يدخل المسار — يُحفظ كبيانات وصفية فقط */
    return d.getFullYear() + '/' + p(d.getMonth() + 1) + '/' + randomId() + '.pdf';
  }

  function uploadFile(field, file) {
    var bucket = field.bucket || 'fluent-uploads';
    var path = storagePath(file);

    if (CFG.previewMode === true) {
      console.info('[Fluent · معاينة] رفع محاكى:', {
        الحقل: field.name, الملف: file.name,
        الحجم: humanSize(file.size), السلة: bucket, المسار: path
      });
      return new Promise(function (res) {
        setTimeout(function () { res({ bucket: bucket, path: path, simulated: true }); }, 700);
      });
    }

    if (!isConfigured()) return Promise.reject(new Error('CONFIG'));

    var base = String(CFG.supabase.url).replace(/\/+$/, '');
    var key = publicKey();
    return fetch(base + '/storage/v1/object/' + encodeURIComponent(bucket) + '/' + path, {
      method: 'POST',
      headers: {
        'apikey': key,
        'Authorization': 'Bearer ' + key,
        'Content-Type': 'application/pdf',
        'x-upsert': 'false',
        'cache-control': '3600'
      },
      body: file
    }).then(function (r) {
      if (r.ok) return { bucket: bucket, path: path };
      return r.text().then(function (t) {
        throw new Error('UPLOAD ' + r.status + ' — ' + t.slice(0, 200));
      });
    });
  }

  /* ============================================================
     حماية من الروبوتات — جاهزة لإضافة Cloudflare Turnstile لاحقًا
     ------------------------------------------------------------
     لا شيء يُحمَّل ولا يُعرض ما دام provider = 'none'.
     عند التفعيل لاحقًا لا يحتاج النموذج أي إعادة بناء.
     ============================================================ */
  function botConfig() {
    return CFG.botProtection || { provider: 'none' };
  }

  function botMount(form) {
    var bp = botConfig();
    if (bp.provider !== 'turnstile' || !bp.siteKey) return;
    var box = el('div', 'fbot cf-turnstile');
    box.setAttribute('data-sitekey', bp.siteKey);
    box.setAttribute('data-language', 'ar');
    box.setAttribute('data-theme', 'dark');
    var actions = form.querySelector('.factions');
    form.insertBefore(box, actions);
    if (window.turnstile && window.turnstile.render) {
      try { window.turnstile.render(box); } catch (e) { /* يتولّاها السكربت تلقائيًا */ }
    }
  }

  function botToken() {
    var bp = botConfig();
    if (bp.provider === 'turnstile' && window.turnstile && window.turnstile.getResponse) {
      try { return window.turnstile.getResponse() || ''; } catch (e) { return ''; }
    }
    return '';
  }

  /* ============================================================
     بناء حقل واحد
     ============================================================ */
  function buildField(f) {
    /* يُنشأ مبكّرًا حتى تستطيع مُعالِجات الأحداث الداخلية الإشارة إليه */
    var item = { field: f };
    var id = uid('f');
    var errId = id + '-err';
    var helpId = id + '-help';

    var wrap = el('div', 'ffield' + (f.width === 'half' ? '' : ' w-full'));
    if (f.type === 'consent') wrap.className = 'ffield w-full fconsent';
    wrap.dataset.name = f.name;
    wrap.dataset.type = f.type;
    if (f.required) wrap.dataset.required = '1';

    var describedBy = [];
    var control = null;

    /* ---- موافقة مفردة ---- */
    if (f.type === 'consent') {
      var lab = el('label', 'fopt');
      var inp = document.createElement('input');
      inp.type = 'checkbox'; inp.id = id; inp.name = f.name;
      var box = el('span', 'fbox is-check');
      box.setAttribute('aria-hidden', 'true');
      var txt = el('span', null, f.label);
      lab.appendChild(inp); lab.appendChild(box); lab.appendChild(txt);
      wrap.appendChild(lab);
      control = inp;

    /* ---- رفع ملف ---- */
    } else if (f.type === 'file') {
      var maxMB = f.maxSizeMB || 5;

      var flab = el('label', 'flabel');
      flab.setAttribute('for', id);
      flab.innerHTML = esc(f.label) + (f.required ? ' <span class="req">*</span>' : ' <span class="opt">(اختياري)</span>');
      wrap.appendChild(flab);

      var fhelp = el('p', 'fhelp');
      fhelp.id = helpId;
      fhelp.textContent = f.help || ('PDF فقط · الحد الأقصى ' + maxMB + ' ميجابايت');
      wrap.appendChild(fhelp);
      describedBy.push(helpId);

      var input = document.createElement('input');
      input.type = 'file';
      input.id = id;
      input.name = f.name;
      input.accept = 'application/pdf,.pdf';
      input.className = 'fdrop-input';
      if (f.required) input.setAttribute('aria-required', 'true');

      var drop = el('div', 'fdrop');
      drop.setAttribute('role', 'button');
      drop.setAttribute('tabindex', '0');
      drop.setAttribute('aria-label', 'اختر ملف ' + f.label + ' بصيغة PDF');
      drop.innerHTML =
        '<span class="fdrop-ico" aria-hidden="true">' + ICON.upload + '</span>' +
        '<span class="fdrop-t">اسحب الملف هنا أو <b>اختر من جهازك</b></span>' +
        '<span class="fdrop-s">PDF · حتى ' + maxMB + ' ميجابايت</span>';
      drop.appendChild(input);

      var chip = el('div', 'ffile');
      chip.hidden = true;

      wrap.appendChild(drop);
      wrap.appendChild(chip);

      /* ---- سلوك الحقل ---- */
      var state = { file: null };

      function paintChip() {
        if (!state.file) { chip.hidden = true; drop.hidden = false; return; }
        drop.hidden = true;
        chip.hidden = false;
        chip.innerHTML =
          '<span class="ffile-ico" aria-hidden="true">' + ICON.doc + '</span>' +
          '<span class="ffile-meta"><b class="ffile-n"></b><span class="ffile-s">' + esc(humanSize(state.file.size)) + '</span></span>' +
          '<span class="ffile-acts">' +
            '<button type="button" class="ffile-b" data-act="replace">استبدال</button>' +
            '<button type="button" class="ffile-x" data-act="remove" aria-label="إزالة الملف">' + ICON.close + '</button>' +
          '</span>';
        chip.querySelector('.ffile-n').textContent = state.file.name;
        chip.querySelector('[data-act=replace]').addEventListener('click', function () { input.click(); });
        chip.querySelector('[data-act=remove]').addEventListener('click', function () {
          state.file = null; input.value = '';
          paintChip();
          paint(item, validate(item));
          drop.focus({ preventScroll: true });
        });
      }

      function take(file) {
        state.file = file || null;
        paintChip();
        paint(item, validate(item));
      }

      input.addEventListener('change', function () { take(input.files && input.files[0]); });
      drop.addEventListener('click', function (e) { if (e.target !== input) input.click(); });
      drop.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
      });
      ['dragenter', 'dragover'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
      });
      ['dragleave', 'dragend'].forEach(function (ev) {
        drop.addEventListener(ev, function () { drop.classList.remove('is-over'); });
      });
      drop.addEventListener('drop', function (e) {
        e.preventDefault();
        drop.classList.remove('is-over');
        var dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length) {
          try { input.files = dt.files; } catch (err) { /* بعض المتصفّحات تمنع الإسناد */ }
          take(dt.files[0]);
        }
      });

      control = input;
      control.__fileState = state;

    /* ---- مجموعة اختيارات ---- */
    } else if (f.type === 'radio' || f.type === 'checkbox') {
      var fs = el('fieldset', 'ffield');
      fs.style.cssText = 'border:0;padding:0;margin:0;display:flex;flex-direction:column;gap:.55rem';
      var lg = el('legend', 'flabel');
      lg.innerHTML = esc(f.label) + (f.required ? ' <span class="req">*</span>' : ' <span class="opt">(اختياري)</span>');
      lg.style.cssText = 'padding:0;margin-bottom:.15rem';
      fs.appendChild(lg);
      if (f.help) { var h1 = el('p', 'fhelp', f.help); h1.id = helpId; fs.appendChild(h1); describedBy.push(helpId); }

      var group = el('div', 'fopts' + (f.inline ? ' is-inline' : ''));
      (f.options || []).forEach(function (o, i) {
        var oid = id + '-' + i;
        var l = el('label', 'fopt');
        var input = document.createElement('input');
        input.type = f.type === 'radio' ? 'radio' : 'checkbox';
        input.name = f.name; input.value = o.value; input.id = oid;
        var b = el('span', 'fbox ' + (f.type === 'radio' ? 'is-radio' : 'is-check'));
        b.setAttribute('aria-hidden', 'true');
        l.appendChild(input); l.appendChild(b); l.appendChild(el('span', null, o.label));
        group.appendChild(l);
      });
      fs.appendChild(group);
      wrap.appendChild(fs);
      control = group;

    /* ---- بقية الأنواع ---- */
    } else {
      var label = el('label', 'flabel');
      label.setAttribute('for', id);
      label.innerHTML = esc(f.label) + (f.required ? ' <span class="req">*</span>' : ' <span class="opt">(اختياري)</span>');
      wrap.appendChild(label);

      if (f.help) { var hp = el('p', 'fhelp', f.help); hp.id = helpId; wrap.appendChild(hp); describedBy.push(helpId); }

      if (f.type === 'textarea') {
        control = document.createElement('textarea');
        if (f.rows) control.rows = f.rows;
      } else if (f.type === 'select') {
        control = document.createElement('select');
        var ph = document.createElement('option');
        ph.value = ''; ph.textContent = f.placeholder || 'اختر…';
        ph.disabled = true; ph.selected = true;
        control.appendChild(ph);
        (f.options || []).forEach(function (o) {
          var op = document.createElement('option');
          op.value = o.value; op.textContent = o.label;
          control.appendChild(op);
        });
      } else {
        control = document.createElement('input');
        control.type = f.type || 'text';
      }

      control.className = 'fctrl';
      control.id = id;
      control.name = f.name;
      if (f.placeholder && f.type !== 'select') control.placeholder = f.placeholder;
      if (f.autocomplete) control.autocomplete = f.autocomplete;
      if (f.maxlength) control.maxLength = f.maxlength;
      if (f.min != null) control.min = f.min;
      if (f.max != null) control.max = f.max;
      if (f.dir) control.dir = f.dir;
      if (f.type === 'email' || f.type === 'url') control.dir = 'ltr';
      if (f.type === 'tel') control.inputMode = 'tel';
      if (f.required) control.setAttribute('aria-required', 'true');

      wrap.appendChild(control);

      /* عدّاد أحرف للنصوص الطويلة */
      if (f.type === 'textarea' && f.maxlength) {
        var counter = el('span', 'fcount');
        var setCount = function () {
          var n = control.value.length;
          counter.textContent = n + ' / ' + f.maxlength;
          counter.classList.toggle('is-near', n > f.maxlength * 0.9);
        };
        control.addEventListener('input', setCount);
        setCount();
        wrap.appendChild(counter);
      }
    }

    /* رسالة الخطأ */
    var err = el('span', 'ferr');
    err.id = errId;
    err.innerHTML = ICON.err + '<span class="ferr-t"></span>';
    wrap.appendChild(err);
    describedBy.push(errId);

    if (control && control.setAttribute && control.tagName !== 'DIV') {
      control.setAttribute('aria-describedby', describedBy.join(' '));
    }

    item.wrap = wrap;
    item.control = control;
    item.err = err.querySelector('.ferr-t');
    return item;
  }

  /* ============================================================
     قراءة القيمة + التحقّق
     ============================================================ */
  function readValue(item) {
    var f = item.field, c = item.control;
    if (f.type === 'file') return (c.__fileState && c.__fileState.file) || null;
    if (f.type === 'consent') return c.checked;
    if (f.type === 'radio') {
      var r = c.querySelector('input:checked');
      return r ? r.value : '';
    }
    if (f.type === 'checkbox') {
      return Array.prototype.slice.call(c.querySelectorAll('input:checked')).map(function (i) { return i.value; });
    }
    if (f.type === 'number') return c.value === '' ? '' : Number(c.value);
    return (c.value || '').trim();
  }

  function validate(item) {
    var f = item.field, v = readValue(item);

    if (f.type === 'file') {
      if (!v) return f.required ? MSG.fileReq : '';
      var isPdf = v.type === 'application/pdf' || /\.pdf$/i.test(v.name || '');
      if (!isPdf) return MSG.filePdf;
      if (!v.size) return MSG.fileEmpty;
      var maxMB = f.maxSizeMB || 5;
      if (v.size > maxMB * 1024 * 1024) return MSG.fileSize(maxMB);
      return '';
    }

    if (f.type === 'consent') {
      if (f.required && !v) return MSG.consent;
      return '';
    }
    if (f.type === 'checkbox') {
      if (f.required && !v.length) return MSG.pick;
      return '';
    }
    if (f.type === 'radio' || f.type === 'select') {
      if (f.required && !v) return MSG.pick;
      return '';
    }

    if (v === '' || v == null) return f.required ? MSG.required : '';

    if (f.type === 'email' && !RE.email.test(v)) return MSG.email;
    if (f.type === 'tel' && !RE.tel.test(String(v).replace(/[\s\-()]/g, ''))) return MSG.tel;
    if (f.type === 'url' && !RE.url.test(v)) return MSG.url;
    if (f.type === 'number') {
      if (isNaN(v)) return MSG.number;
      if (f.min != null && v < f.min) return MSG.min(f.min);
      if (f.max != null && v > f.max) return MSG.max(f.max);
    }
    if (f.minlength && String(v).length < f.minlength) return MSG.minlength(f.minlength);
    if (f.pattern && !(new RegExp(f.pattern)).test(v)) return f.patternMessage || MSG.required;

    return '';
  }

  function paint(item, msg) {
    item.wrap.classList.toggle('is-err', !!msg);
    item.err.textContent = msg || '';
    if (item.control && item.control.setAttribute && item.control.tagName !== 'DIV') {
      if (msg) item.control.setAttribute('aria-invalid', 'true');
      else item.control.removeAttribute('aria-invalid');
    }
  }

  /* ============================================================
     جسر طبقة المعاينة
     ------------------------------------------------------------
     في وضع المعاينة نكتب الطلب في FluentStore حتى يظهر مباشرة
     في لوحة الإدارة وفي مساحة الطالب. عند الإنتاج تتكفّل Supabase
     بذلك، فتصبح هذه الدالة بلا عمل.
     ============================================================ */
  function persistLocally(schema, row) {
    if (CFG.previewMode !== true || !window.FluentStore) return Promise.resolve();

    var type = schema.submissionType;

    if (schema.table === 'challenge') {
      if (type !== 'challenge') return Promise.resolve();
      return FluentStore.createChallenge({
        reference: row.reference, status: 'new', created_at: new Date().toISOString(),
        org_name: row.org_name, contact_name: row.contact_name,
        email: row.email, phone: row.phone, data: row.data
      });
    }

    if (type !== 'application') return Promise.resolve();

    return FluentStore.createApplication({
      reference: row.reference, status: 'received', created_at: new Date().toISOString(),
      full_name: row.full_name, email: row.email, phone: row.phone, data: row.data
    }).then(function (rec) {
      /* حساب الطالب جاهز فورًا: تقديم → مساحتي */
      return FluentStore.signInStudent(rec.email).then(function () { return rec; });
    });
  }

  /* ============================================================
     المحرّك
     ============================================================ */
  function mount(opts) {
    var host = typeof opts.mount === 'string' ? document.querySelector(opts.mount) : opts.mount;
    var schema = opts.schema;
    if (!host || !schema) return null;

    var startedAt = Date.now();
    var stepIndex = 0;
    var items = [];
    var stepItems = [];

    host.innerHTML = '';

    /* شريط وضع المعاينة — بلغة الزائر، بلا أي مصطلح تقني */
    if (CFG.previewMode === true) {
      var pv = el('div', 'fpreview');
      pv.innerHTML = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4.5M12 16h.01"/></svg>' +
        '<span><b>نسخة تجريبية.</b> جرّب النموذج بحرّية — لن يُحفظ ما تكتبه ولن يصل إلى أحد.</span>';
      host.appendChild(pv);
    }

    /* تنبيه الأخطاء العامة */
    var alertBox = el('div', 'falert');
    alertBox.style.display = 'none';
    alertBox.setAttribute('role', 'alert');
    host.appendChild(alertBox);

    /* مؤشّر الخطوات */
    var steps = schema.steps || [];
    var multi = steps.length > 1;
    var stepsBar = null;
    if (multi) {
      stepsBar = el('div', 'fsteps');
      stepsBar.setAttribute('aria-hidden', 'true');
      steps.forEach(function (s, i) {
        var d = el('div', 'fstep');
        d.innerHTML = '<b>' + ('0' + (i + 1)).slice(-2) + '</b>' + esc(s.title || '');
        stepsBar.appendChild(d);
      });
      host.appendChild(stepsBar);
    }

    /* النموذج */
    var form = document.createElement('form');
    form.noValidate = true;
    form.setAttribute('novalidate', '');

    var live = el('p', 'fmeta');
    live.setAttribute('aria-live', 'polite');
    live.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)';
    form.appendChild(live);

    steps.forEach(function (s, i) {
      var panel = el('div', 'fstep-panel');
      panel.dataset.step = i;
      if (i !== 0) panel.hidden = true;

      if (multi && s.hint) {
        var hint = el('p', 'fhelp');
        hint.textContent = s.hint;
        hint.style.marginBottom = '1.4rem';
        panel.appendChild(hint);
      }

      var grid = el('div', 'fgrid');
      var group = [];
      (s.fields || []).forEach(function (f) {
        var item = buildField(f);
        grid.appendChild(item.wrap);
        items.push(item);
        group.push(item);

        /* تحقّق عند الخروج من الحقل، وتنظيف الخطأ أثناء الكتابة */
        var c = item.control;
        var revalidate = function () { paint(item, validate(item)); };
        if (f.type === 'radio' || f.type === 'checkbox') {
          c.addEventListener('change', revalidate);
        } else {
          c.addEventListener('blur', function () {
            if (f.type === 'url' && c.value && !/^https?:\/\//i.test(c.value) && c.value.indexOf('.') > 0) {
              c.value = 'https://' + c.value.replace(/^\/+/, '');
            }
            revalidate();
          });
          c.addEventListener('change', revalidate);
          c.addEventListener('input', function () {
            if (item.wrap.classList.contains('is-err')) revalidate();
          });
        }
      });

      panel.appendChild(grid);
      stepItems.push(group);
      form.appendChild(panel);
    });

    /* مصيدة السبام */
    var hp = el('div', 'fhp');
    hp.setAttribute('aria-hidden', 'true');
    hp.innerHTML = '<label>لا تملأ هذا الحقل<input type="text" name="fl_website" tabindex="-1" autocomplete="off"></label>';
    form.appendChild(hp);

    /* الأزرار */
    var actions = el('div', 'factions');
    var backBtn = el('button', 'btn btn-outline');
    backBtn.type = 'button';
    backBtn.innerHTML = ICON.arrowBack + '<span class="btn-label">السابق</span>';
    backBtn.hidden = true;

    var nextBtn = el('button', 'btn btn-primary');
    nextBtn.type = 'submit';
    nextBtn.innerHTML = '<span class="btn-label">' + (multi ? 'التالي' : (schema.submitLabel || 'أرسل الطلب')) + '</span><span class="spin" aria-hidden="true"></span>' + ICON.arrow;

    var meta = el('p', 'fmeta');
    meta.innerHTML = 'الحقول المعلَّمة بـ <span style="color:var(--signal)">*</span> مطلوبة.';

    actions.appendChild(backBtn);
    actions.appendChild(meta);
    var spacer = el('span', 'spacer');
    actions.appendChild(spacer);
    actions.appendChild(nextBtn);
    form.appendChild(actions);
    botMount(form);
    host.appendChild(form);

    /* ---------- التنقّل بين الخطوات ---------- */
    function isLast() { return stepIndex >= steps.length - 1; }

    function renderNav() {
      backBtn.hidden = stepIndex === 0;
      nextBtn.querySelector('.btn-label').textContent =
        isLast() ? (schema.submitLabel || 'أرسل الطلب') : 'التالي';
      if (stepsBar) {
        Array.prototype.forEach.call(stepsBar.children, function (n, i) {
          n.classList.toggle('is-on', i === stepIndex);
          n.classList.toggle('is-done', i < stepIndex);
        });
      }
      Array.prototype.forEach.call(form.querySelectorAll('.fstep-panel'), function (p, i) {
        p.hidden = i !== stepIndex;
      });
      if (multi) {
        live.textContent = 'الخطوة ' + (stepIndex + 1) + ' من ' + steps.length +
          (steps[stepIndex].title ? ' — ' + steps[stepIndex].title : '');
      }
    }

    function checkStep(i) {
      var bad = null;
      stepItems[i].forEach(function (item) {
        var m = validate(item);
        paint(item, m);
        if (m && !bad) bad = item;
      });
      if (bad) {
        var target = bad.control.tagName === 'DIV'
          ? bad.control.querySelector('input')
          : bad.control;
        bad.wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (target && target.focus) setTimeout(function () { target.focus({ preventScroll: true }); }, 220);
        return false;
      }
      return true;
    }

    backBtn.addEventListener('click', function () {
      if (stepIndex === 0) return;
      stepIndex--;
      renderNav();
      host.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    /* ---------- بناء الصف المُرسَل ---------- */
    function buildRow() {
      var data = {};
      items.forEach(function (item) {
        if (item.field.type === 'file') return;   /* الملفات تُعالَج بالرفع، لا بالتسلسل */
        var v = readValue(item);
        if (v === '' || v == null || (Array.isArray(v) && !v.length)) return;
        data[item.field.name] = v;
      });

      var row = {
        reference: makeReference(),
        submission_type: schema.submissionType || 'submission',
        data: data,
        source: location.pathname.replace(/^.*\//, '') || 'index.html',
        user_agent: navigator.userAgent.slice(0, 300)
      };

      /* رمز Turnstile إن كان مفعّلًا — تتحقّق منه الدالة الطرفية لاحقًا */
      var tok = botToken();
      if (tok) row.bot_token = tok;

      /* نسخ الحقول الأساسية إلى أعمدة حقيقية لسهولة الفرز في Supabase */
      var cols = schema.columns || {};
      Object.keys(cols).forEach(function (fieldName) {
        if (data[fieldName] != null && data[fieldName] !== '') row[cols[fieldName]] = data[fieldName];
      });

      return row;
    }

    /* ---------- الإرسال ---------- */
    function showAlert(title, text, retryable) {
      alertBox.innerHTML = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4.5M12 16h.01"/></svg>' +
        '<span><b>' + esc(title) + '</b>' + text + '</span>';
      alertBox.style.display = 'flex';
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
      if (!retryable) nextBtn.disabled = true;
    }

    function showDone(reference) {
      var panel = el('div', 'fdone');
      panel.setAttribute('role', 'status');
      panel.innerHTML =
        '<span class="fdone-mark" aria-hidden="true"><svg viewBox="0 0 990 500"><use href="#fl-mark"/></svg></span>' +
        '<h2>' + esc(schema.doneTitle || 'وصلنا طلبك.') + '</h2>' +
        '<p>' + esc(schema.doneText || 'يراجع فريق Fluent الطلب ويعود لك عبر البريد الإلكتروني خلال أيام عمل قليلة.') + '</p>' +
        '<span class="fref">الرقم المرجعي <b dir="ltr">' + esc(reference) + '</b></span>' +
        '<div class="fdone-actions">' +
        (schema.portalCta
          ? '<a class="btn btn-primary" href="' + esc(schema.portalCta.href) + '">' + esc(schema.portalCta.label) + ICON.arrow + '</a>' +
            '<a class="btn btn-outline" href="' + (CFG.homeHref || 'index.html') + '">العودة إلى Fluent</a>'
          : '<a class="btn btn-primary" href="' + (CFG.homeHref || 'index.html') + '">العودة إلى Fluent' + ICON.arrow + '</a>') +
        (schema.doneSecondary
          ? '<a class="btn btn-outline" href="' + schema.doneSecondary.href + '">' + esc(schema.doneSecondary.label) + '</a>'
          : '') +
        '</div>';

      host.innerHTML = '';
      host.appendChild(panel);
      document.body.classList.add('is-done');
      host.scrollIntoView({ behavior: 'smooth', block: 'center' });
      var h = panel.querySelector('h2');
      h.setAttribute('tabindex', '-1');
      h.focus({ preventScroll: true });
      if (typeof opts.onSuccess === 'function') opts.onSuccess(reference);
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!checkStep(stepIndex)) return;

      if (!isLast()) {
        stepIndex++;
        renderNav();
        host.scrollIntoView({ behavior: 'smooth', block: 'start' });
        var firstCtrl = form.querySelector('.fstep-panel:not([hidden]) .fctrl, .fstep-panel:not([hidden]) input');
        if (firstCtrl) setTimeout(function () { firstCtrl.focus({ preventScroll: true }); }, 320);
        return;
      }

      /* تحقّق نهائي لكل الخطوات */
      for (var i = 0; i < steps.length; i++) {
        if (!checkStep(i)) {
          if (i !== stepIndex) { stepIndex = i; renderNav(); checkStep(i); }
          return;
        }
      }

      /* مصيدة السبام + زمن التعبئة */
      var trap = form.querySelector('[name="fl_website"]');
      var elapsed = (Date.now() - startedAt) / 1000;
      if ((trap && trap.value) || elapsed < (CFG.minFillSeconds || 4)) {
        showAlert('تعذّر إرسال الطلب.', ' خذ وقتك في تعبئة النموذج ثم أعد المحاولة.', true);
        startedAt = Date.now();
        return;
      }

      alertBox.style.display = 'none';
      nextBtn.dataset.loading = '1';
      nextBtn.setAttribute('aria-busy', 'true');
      backBtn.disabled = true;

      var row = buildRow();
      var label = nextBtn.querySelector('.btn-label');
      var originalLabel = label.textContent;

      /* الملفات المرفقة في كل الخطوات */
      var pending = items.filter(function (it) {
        return it.field.type === 'file' && readValue(it);
      });

      if (pending.length) label.textContent = 'جارٍ رفع الملف…';

      Promise.all(pending.map(function (it) {
        var file = readValue(it);
        return uploadFile(it.field, file).then(function (res) {
          /* الصف يحمل المسار فقط — لا الملف ولا محتواه */
          row.data[it.field.name] = {
            file_name: file.name,
            size_bytes: file.size,
            bucket: res.bucket,
            path: res.path
          };
          if (it.field.column) row[it.field.column] = res.bucket + '/' + res.path;
        });
      }))
        .then(function () {
          label.textContent = 'جارٍ الإرسال…';
          return send(schema, row);
        })
        .then(function () { return persistLocally(schema, row); })
        .then(function () { showDone(row.reference); })
        .catch(function (err) {
          console.error('[Fluent] فشل الإرسال:', err);
          nextBtn.dataset.loading = '0';
          nextBtn.removeAttribute('data-loading');
          nextBtn.removeAttribute('aria-busy');
          backBtn.disabled = false;
          label.textContent = originalLabel;

          if (err && String(err.message).indexOf('UPLOAD') === 0) {
            showAlert('تعذّر رفع الملف.',
              ' تأكد أن الملف بصيغة PDF وضمن الحجم المسموح، ثم أعد المحاولة.', true);
            return;
          }

          if (err && err.message === 'CONFIG') {
            console.error('[Fluent] الإرسال غير مفعّل: لم تُضبط بيانات Supabase في assets/js/fluent-config.js، و previewMode = false.');
            showAlert('التسجيل غير متاح حاليًا.',
              ' نعمل على المشكلة. راسلنا في هذه الأثناء على ' +
              '<a href="mailto:' + esc(CFG.contactEmail || 'info@fluent.com') + '" dir="ltr">' +
              esc(CFG.contactEmail || 'info@fluent.com') + '</a>.', false);
          } else {
            showAlert('ما وصل الطلب.',
              ' تحقّق من الاتصال وأعد المحاولة. إذا تكرّرت المشكلة راسلنا على ' +
              '<a href="mailto:' + esc(CFG.contactEmail || 'info@fluent.com') + '" dir="ltr">' +
              esc(CFG.contactEmail || 'info@fluent.com') + '</a>.', true);
          }
        });
    });

    renderNav();
    return { form: form, items: items };
  }

  /* ============================================================
     تشغيل الصفحة
     ============================================================ */
  function init(journey, hostSel, statusSel) {
    var host = document.querySelector(hostSel);
    var statusEl = statusSel ? document.querySelector(statusSel) : null;
    if (!host) return;

    var r = resolveJourney(journey);

    /* عنوان البطاقة يتغيّر بتغيّر حالة التسجيل */
    var head = document.getElementById('form-h');
    if (head && r.copy.cardTitle) head.textContent = r.copy.cardTitle;

    /* ملاحظات جانبية تخصّ حالة التسجيل المفتوح فقط */
    Array.prototype.forEach.call(document.querySelectorAll('[data-when="open"]'), function (n) {
      n.hidden = (r.state !== 'open');
    });

    /* شارة الحالة */
    if (statusEl) {
      statusEl.className = 'fstatus is-' + r.state;
      statusEl.innerHTML = '<i aria-hidden="true"></i>' + esc(r.copy.badge || '');
      statusEl.hidden = !r.copy.badge;
    }

    /* ---------- مغلق ----------
       الزر يفتح نموذج تنبيه حقيقي داخل الصفحة (اسم، بريد، جوال)
       ويُخزَّن بنوع notification. لا زر شكلي بلا وظيفة. */
    if (r.state === 'closed' || !r.schema) {
      host.innerHTML = '';
      var shut = el('div', 'fshut');
      shut.innerHTML =
        '<h2>' + esc(r.copy.title || 'التسجيل مغلق حاليًا.') + '</h2>' +
        '<p>' + esc(r.copy.text || '') + '</p>';

      var slot = el('div', 'fslot');

      if (r.notify) {
        var open = el('button', 'btn btn-primary');
        open.type = 'button';
        open.setAttribute('aria-expanded', 'false');
        open.setAttribute('aria-controls', 'notify-slot');
        open.innerHTML = '<span class="btn-label">' + esc(r.copy.notifyCta || 'نبّهني عند فتح التسجيل') + '</span>' + ICON.arrow;
        shut.appendChild(open);
        slot.id = 'notify-slot';

        open.addEventListener('click', function () {
          open.hidden = true;
          open.setAttribute('aria-expanded', 'true');
          slot.classList.add('is-open');
          var head = el('h3', 'd3');
          head.textContent = r.copy.notifyTitle || 'نبّهني عند فتح التسجيل';
          head.style.marginBottom = '.6rem';
          var sub = el('p', 'fhelp');
          sub.textContent = r.copy.notifyText || 'اتركنا نراسلك أول ما يُفتح التسجيل.';
          sub.style.marginBottom = '1.5rem';
          slot.appendChild(head);
          slot.appendChild(sub);
          var box = el('div');
          slot.appendChild(box);
          mount({ mount: box, schema: r.notify });
          head.setAttribute('tabindex', '-1');
          head.focus({ preventScroll: true });
          slot.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
      } else {
        var mail = el('a', 'btn btn-primary');
        mail.href = 'mailto:' + (CFG.contactEmail || 'info@fluent.com') +
          '?subject=' + encodeURIComponent('إشعار فتح التسجيل — Fluent');
        mail.innerHTML = 'راسلنا' + ICON.arrow;
        shut.appendChild(mail);
      }

      host.appendChild(shut);
      host.appendChild(slot);
      return;
    }

    /* قائمة انتظار — نعرض تمهيدًا قبل النموذج القصير */
    if (r.state === 'waitlist') {
      var intro = el('div', 'fshut');
      intro.style.marginBottom = '2rem';
      intro.innerHTML =
        '<h2>' + esc(r.copy.title || '') + '</h2>' +
        '<p>' + esc(r.copy.text || '') + '</p>';
      host.parentNode.insertBefore(intro, host);
    }

    mount({ mount: host, schema: r.schema });
  }

  /* ============================================================
     مساعدات عامة للصفحات (هيدر ثابت + ظهور تدريجي)
     ============================================================ */
  function shell() {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var yr = document.getElementById('yr');
    if (yr) yr.textContent = new Date().getFullYear();

    var hdr = document.getElementById('hdr');
    if (hdr) {
      var onScroll = function () { hdr.classList.toggle('stuck', window.scrollY > 24); };
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
    }

    var reveals = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window) || reduce) {
      Array.prototype.forEach.call(reveals, function (n) { n.classList.add('in'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
        });
      }, { rootMargin: '0px 0px -12% 0px', threshold: .15 });
      Array.prototype.forEach.call(reveals, function (n) { io.observe(n); });
    }
  }

  window.FluentForm = {
    init: init,
    mount: mount,
    shell: shell,
    resolve: resolveJourney,
    isConfigured: isConfigured
  };

})(window, document);
