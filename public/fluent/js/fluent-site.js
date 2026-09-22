/* ============================================================
   FLUENT — Public site script (redesign)
   Copied verbatim from the approved prototype (index.html <script>).
   ============================================================ */
(function(){
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* year */
  var yr=document.getElementById('yr'); if(yr) yr.textContent=new Date().getFullYear();

  /* sticky header */
  var hdr=document.getElementById('hdr');
  var onScroll=function(){ hdr.classList.toggle('stuck', window.scrollY>24); };
  onScroll(); window.addEventListener('scroll', onScroll, {passive:true});

  /* scroll reveals */
  var items=document.querySelectorAll('.reveal, .bridge');
  if(!('IntersectionObserver' in window) || reduce){
    items.forEach(function(el){ el.classList.add('in'); });
  } else {
    var io=new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
      });
    },{rootMargin:'0px 0px -12% 0px', threshold:.15});
    items.forEach(function(el){ io.observe(el); });
  }

  /* ---------- Counting the first-cohort numbers ----------
     تعدّ مرة واحدة عند أول ظهور. مع تقليل الحركة تظهر القيمة
     النهائية مباشرة بلا عدّ. */
  (function(){
    var els = Array.prototype.slice.call(document.querySelectorAll('[data-count]'));
    if(!els.length) return;

    function paint(el, val){
      var slot = el.querySelector('[data-n]');
      if(slot) slot.textContent = val; else el.textContent = val;
    }
    function run(el){
      var target = parseFloat(el.dataset.count) || 0;
      if(reduce){ paint(el, target); return; }
      var dur = 900, t0 = null;
      function step(ts){
        if(!t0) t0 = ts;
        var p = Math.min((ts - t0) / dur, 1);
        paint(el, Math.round(target * (1 - Math.pow(1 - p, 3))));
        if(p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    if(!('IntersectionObserver' in window) || reduce){
      els.forEach(run);
      return;
    }
    var co = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(!e.isIntersecting) return;
        run(e.target);
        co.unobserve(e.target);
      });
    }, { threshold: .4 });
    els.forEach(function(el){ co.observe(el); });
  })();

  /* ---------- Partners marquee ----------
     حلقة لانهائية سلسة بلا قفزة: نكرّر مجموعة الشعارات حتى تملأ الشريط
     مرتين، ثم نحرّك الشريط بمقدار دورة واحدة ونعيده إلى الصفر.
     السرعة ثابتة بالبكسل/الثانية مهما كان عدد الشعارات. */
  (function(){
    var mq = document.querySelector('[data-marquee]');
    if(!mq) return;
    var track = mq.querySelector('.mq-track');
    var originals = Array.prototype.slice.call(track.children);
    if(!originals.length) return;

    var SPEED = 26;        /* بكسل في الثانية — بطيء ومتّزن */
    var SLOW  = 0.14;      /* نسبة التباطؤ عند المرور بالمؤشر */
    var cycle = 0, off = 0, cur = 0, target = SPEED, last = 0, raf = null, running = false;

    function build(){
      Array.prototype.forEach.call(track.querySelectorAll('[data-clone]'), function(n){ n.remove(); });
      var guard = 0;
      while((track.scrollWidth < mq.offsetWidth * 2 || track.children.length < originals.length * 2) && guard++ < 14){
        originals.forEach(function(n){
          var c = n.cloneNode(true);
          c.setAttribute('data-clone','1');
          c.setAttribute('aria-hidden','true');
          track.appendChild(c);
        });
      }
      var firstClone = track.children[originals.length];
      cycle = firstClone ? (firstClone.offsetLeft - track.children[0].offsetLeft) : 0;
      if(off > cycle) off = 0;
    }

    function frame(ts){
      if(!last) last = ts;
      var dt = Math.min((ts - last) / 1000, .05);
      last = ts;
      cur += (target - cur) * 0.05;          /* تباطؤ وتسارع ناعم */
      off += cur * dt;
      if(cycle > 0 && off >= cycle) off -= cycle;
      track.style.transform = 'translateX(' + (off - cycle) + 'px)';
      raf = requestAnimationFrame(frame);
    }

    function start(){ if(running || reduce || !cycle) return; running = true; last = 0; raf = requestAnimationFrame(frame); }
    function stop(){ running = false; if(raf) cancelAnimationFrame(raf); raf = null; }

    build();

    /* الصور قد لا تكون قد حُمِّلت بعد لحظة البناء الأولى — نعيد القياس بعد تحميلها */
    var imgs = Array.prototype.slice.call(track.querySelectorAll('img'));
    var left = imgs.filter(function(im){ return !im.complete; }).length;
    if(left){
      imgs.forEach(function(im){
        if(im.complete) return;
        var done = function(){ if(--left <= 0) build(); };
        im.addEventListener('load', done, {once:true});
        im.addEventListener('error', done, {once:true});
      });
    }

    if(reduce){
      track.style.transform = 'none';
      return;
    }

    mq.addEventListener('mouseenter', function(){ target = SPEED * SLOW; });
    mq.addEventListener('mouseleave', function(){ target = SPEED; });
    mq.addEventListener('focusin',    function(){ target = SPEED * SLOW; });
    mq.addEventListener('focusout',   function(){ target = SPEED; });

    if('IntersectionObserver' in window){
      new IntersectionObserver(function(en){ en[0].isIntersecting ? start() : stop(); }, {threshold:0}).observe(mq);
    } else { start(); }
    document.addEventListener('visibilitychange', function(){ document.hidden ? stop() : start(); });

    var mrt = null;
    window.addEventListener('resize', function(){
      clearTimeout(mrt);
      mrt = setTimeout(function(){ build(); }, 180);
    });
  })();

  /* ---------- Hero: brand wave-line field ----------
     Derived from Fluent's identity artwork (fine layered wave lines)
     and the continuous curve of the logo mark. */
  var cv=document.getElementById('wave');
  if(!cv) return;
  var ctx=cv.getContext('2d');
  var w=0,h=0,dpr=1,lines=[],running=false,t=0,raf=null;

  function build(){
    dpr=Math.min(window.devicePixelRatio||1,2);
    var r=cv.getBoundingClientRect();
    w=Math.max(r.width,320); h=Math.max(r.height,420);
    cv.width=Math.round(w*dpr); cv.height=Math.round(h*dpr);
    ctx.setTransform(dpr,0,0,dpr,0,0);
    var n = w<720 ? 16 : (w<1200 ? 24 : 34);
    lines=[];
    for(var i=0;i<n;i++){
      var p=i/(n-1);
      lines.push({
        p:p,
        off:p*0.55,
        amp:(h*0.115)*(0.5+p*0.85),
        y:h*(0.46+p*0.40),
        sp:0.16+p*0.10,
        wob:0.9+p*0.5
      });
    }
  }

  function draw(){
    ctx.clearRect(0,0,w,h);
    ctx.lineCap='round';
    var step=Math.max(10, w/70);
    for(var i=0;i<lines.length;i++){
      var L=lines[i], p=L.p;
      ctx.beginPath();
      for(var x=-20;x<=w+20;x+=step){
        var u=x/w;
        var y = L.y
          + Math.sin(u*2.35 + t*L.sp + L.off*6.28)*L.amp
          + Math.sin(u*4.7  - t*L.sp*0.7 + L.off*3.1)*L.amp*0.34*L.wob
          + Math.sin(u*1.15 + t*0.08)*L.amp*0.22;
        if(x<=-20) ctx.moveTo(x,y); else ctx.lineTo(x,y);
      }
      /* colour ramp: brand yellow through neutral light gray */
      var yellowness=Math.max(0,1-p*1.35);
      var rr=Math.round(247*yellowness+244*(1-yellowness));
      var gg=Math.round(200*yellowness+244*(1-yellowness));
      var bb=Math.round(0*yellowness+244*(1-yellowness));
      ctx.strokeStyle='rgba('+rr+','+gg+','+bb+','+(0.07+0.30*(1-p)).toFixed(3)+')';
      ctx.lineWidth=1;
      ctx.stroke();
    }
  }

  function loop(){
    t+=0.0055; draw();
    raf=requestAnimationFrame(loop);
  }
  function start(){ if(running||reduce) return; running=true; raf=requestAnimationFrame(loop); }
  function stop(){ running=false; if(raf) cancelAnimationFrame(raf); raf=null; }

  build(); draw();
  if(!reduce){
    if('IntersectionObserver' in window){
      new IntersectionObserver(function(en){
        en[0].isIntersecting ? start() : stop();
      },{threshold:0}).observe(cv);
    } else { start(); }
    document.addEventListener('visibilitychange', function(){
      document.hidden ? stop() : start();
    });
  }

  var rt=null;
  window.addEventListener('resize', function(){
    clearTimeout(rt);
    rt=setTimeout(function(){ build(); draw(); },160);
  });
})();
