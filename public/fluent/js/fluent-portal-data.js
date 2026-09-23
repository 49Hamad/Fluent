/* ============================================================
   FLUENT — Student portal data layer (REAL, Phase 2)
   ------------------------------------------------------------
   Implements the small FluentStore API that the approved portal UI
   (fluent-portal.js) reads — backed by Laravel:

     GET  FLUENT_PORTAL.dataUrl    → the signed-in student's own data only
     POST FLUENT_PORTAL.logoutUrl  → ends the student session

   Nothing is kept in localStorage / sessionStorage. The server decides
   what the student may see (no internal notes, no CV path, no history).
   ============================================================ */

(function () {
  'use strict';

  var P = window.FLUENT_PORTAL || {};

  /* The 7 real statuses (same ids as App\Enums\ApplicationStatus) */
  var APP_STATUS = [
    { id:'received',             label:'تم استلام الطلب', tone:'neutral' },
    { id:'review',               label:'قيد المراجعة',    tone:'neutral' },
    { id:'interview',            label:'مرشح للمقابلة',   tone:'signal'  },
    { id:'preliminary_accepted', label:'مقبول مبدئيًا',   tone:'signal'  },
    { id:'final_accepted',       label:'مقبول نهائيًا',   tone:'good'    },
    { id:'waitlist',             label:'قائمة الانتظار',  tone:'hold'    },
    { id:'rejected',             label:'غير مقبول',       tone:'soft'    }
  ];
  var TRACK = ['received', 'review', 'interview'];

  function statusMeta(list, id) {
    for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
    return { id: id, label: id, tone: 'neutral' };
  }

  var cache = null;
  var pending = null;

  function load() {
    var url = P.dataUrl + (P.application ? ('?application=' + encodeURIComponent(P.application)) : '');
    return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' }, cache: 'no-store' })
      .then(function (r) {
        if (r.status === 401) { location.replace(P.loginUrl); return new Promise(function () {}); }
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (d) { cache = d; return d; });
  }

  window.FluentStore = {
    APP_STATUS: APP_STATUS,
    TRACK: TRACK,
    statusMeta: statusMeta,

    /* The server session is the real authentication; this only feeds the UI. */
    session: function () {
      return { role: 'student', application_id: 'current', name: (P.studentName || '') };
    },
    getApplication: function () {
      pending = load();
      return pending.then(function (d) { return d.application; });
    },
    getSettings: function () {
      return (pending || load()).then(function (d) {
        var a = d.application || {};
        return { cohort: a.cohort || { name: a.cohort_name } };
      });
    },
    applications: function () { return (cache && cache.applications) || []; },
    signOut: function () {
      return fetch(P.logoutUrl, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': P.csrf || '', 'X-Requested-With': 'XMLHttpRequest' }
      }).catch(function () {});
    }
  };

  window.FluentNav = {
    go:      function (page) { location.href = page; },
    replace: function (page) { location.replace(page); }
  };
})();
