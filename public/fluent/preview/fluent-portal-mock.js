/* ============================================================
   FLUENT — Student portal MOCK data layer (development preview only)
   ------------------------------------------------------------
   Loaded ONLY by the /preview/portal route, which exists only when
   FLUENT_FRONTEND_PREVIEW is enabled (never in production).

   It implements the small part of the prototype's FluentStore API
   that fluent-portal.js reads, entirely in memory:
     - no localStorage / sessionStorage / cookies
     - no network, no database, no real student data
     - no authentication: the "session" below is a fixed fake object

   In a later phase fluent-portal.js will read from Laravel instead,
   and this file will not be loaded at all.
   ============================================================ */

(function () {
  'use strict';

  var P = window.FLUENT_PREVIEW || {};

  /* Status list + track — identical to the prototype */
  var APP_STATUS = [
    { id:'received',  label:'تم استلام الطلب', tone:'neutral' },
    { id:'review',    label:'قيد المراجعة',    tone:'neutral' },
    { id:'interview', label:'مرشح للمقابلة',   tone:'signal'  },
    { id:'accepted',  label:'مقبول',           tone:'good'    },
    { id:'waitlist',  label:'قائمة الانتظار',  tone:'hold'    },
    { id:'rejected',  label:'غير مقبول',       tone:'soft'    }
  ];
  var TRACK = ['received', 'review', 'interview'];

  function statusMeta(list, id) {
    for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
    return { id: id, label: id, tone: 'neutral' };
  }

  var status = statusMeta(APP_STATUS, P.state).id === P.state ? P.state : 'review';

  /* Clearly fictional example record */
  var MOCK_APP = {
    id: 'preview-1',
    reference: 'FL-PREVIEW-0001',
    status: status,
    created_at: '2026-09-01T09:00:00+03:00',
    full_name: 'نورة مثال',
    email: 'student@example.com',
    phone: '+966500000000',
    data: {
      city: 'بريدة (مثال)',
      university: 'جامعة القصيم (مثال)',
      major: 'إدارة أعمال (مثال)',
      cv: { file_name: 'CV-example.pdf' }
    }
  };

  /* Example cohort details so the acceptance state can be reviewed.
     Every value is marked as an example — none of it is real. */
  var MOCK_SETTINGS = {
    registration: 'open',
    cohort: {
      name:         'الدفعة الثانية (مثال)',
      startDate:    'الأحد 18 أكتوبر 2026 (مثال)',
      time:         '9:00 ص – 3:00 م (مثال)',
      location:     'بريدة — مقر Fluent (مثال)',
      instructions: 'مثال على التعليمات: احضر قبل الموعد بـ 15 دقيقة، وستصلك تفاصيل فريقك ودورك قبل البداية بيومين.',
      bring:        'مثال: جهاز حاسب محمول، وهوية وطنية، ودفتر ملاحظات.'
    }
  };

  var SESSION = { role: 'student', application_id: MOCK_APP.id, name: MOCK_APP.full_name };

  function resolve(v) { return Promise.resolve(v); }

  window.FluentStore = {
    APP_STATUS: APP_STATUS,
    TRACK: TRACK,
    statusMeta: statusMeta,
    session: function () { return SESSION; },
    getApplication: function () { return resolve(MOCK_APP); },
    getSettings: function () { return resolve(MOCK_SETTINGS); },
    signOut: function () { return resolve(true); }
  };

  window.FluentNav = {
    go:      function (page) { location.href = page; },
    replace: function (page) { location.replace(page); }
  };

  /* Development-only state switcher, appended to the prototype's
     demo bar after fluent-portal.js has written its own notice. */
  document.addEventListener('DOMContentLoaded', function () {
    var bar = document.getElementById('demobar');
    if (!bar) return;
    bar.hidden = false;
    var wrap = document.createElement('span');
    wrap.className = 'pv-states';
    wrap.innerHTML = '<span class="pv-states-l">معاينة الحالة:</span>';
    APP_STATUS.forEach(function (s) {
      var a = document.createElement('a');
      a.href = '?state=' + s.id;
      a.textContent = s.label;
      if (s.id === status) { a.className = 'is-on'; a.setAttribute('aria-current', 'true'); }
      wrap.appendChild(a);
    });
    bar.appendChild(wrap);
  });
})();
