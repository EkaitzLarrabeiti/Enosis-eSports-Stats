<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inicio | Enosis eSports</title>
    @vite('resources/css/landing.css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

</head>
<body class="landing-body">

@include('layouts.partials.header')

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- ══════════════════════════ HERO ══════════════════════════ -->
  <section id="hero">
    <div class="hero-bg-img" style="--hero-bg-image: url('{{ asset('img/silverstone1.png') }}');"></div>
    <div class="hero-grid"></div>
    <div class="speed-lines" id="speedLines"></div>
    <div class="hero-bg-text">ENOSIS</div>

    <div class="telem-bar">
      <span class="live-dot"></span>
      <span>TELEMETRÍA: ACTIVA</span>
      <span>VUELTA RÁPIDA: <span class="telem-val" id="lapTime">1:32.847</span></span>
      <span>SECTOR: <span class="telem-val">S3</span></span>
      <span>RPM: <span class="telem-val" id="rpmVal">12840</span></span>
      <span>COMBUSTIBLE: <span class="telem-val">67%</span></span>
      <span>NEUMÁTICOS: <span class="telem-val">Blandos</span></span>
    </div>

    <div class="hero-content">
      <p class="hero-label">// SIMRACING TEAM — EST. 2021</p>
      <h1 class="hero-title">
        <img src="{{ asset('img/Enosis-WhiteRed.png') }}" alt="Enosis" class="hero-title-logo">
        <span class="line2">e<span class="accent">S</span>ports</span>
      </h1>
      <p class="hero-sub">Competición de alto rendimiento en los circuitos virtuales más exigentes del mundo. Velocidad, precisión y trabajo en equipo.</p>
      <div class="hero-ctas">
        <a href="#team" class="btn-primary">Conoce el equipo</a>
        <a href="#results" class="btn-secondary">Ver resultados</a>
      </div>
    </div>

    <div class="stat-strip">
      <div class="stat-item">
        <div class="stat-num"><span class="count-up" data-target="48">0</span></div>
        <div class="stat-lbl">VICTORIAS</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span class="count-up" data-target="12">0</span></div>
        <div class="stat-lbl">CAMPEONATOS</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span class="count-up" data-target="8">0</span></div>
        <div class="stat-lbl">PILOTOS</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><span class="count-up" data-target="3">0</span>+</div>
        <div class="stat-lbl">AÑOS EN PISTA</div>
      </div>
    </div>
  </section>

  <!-- ══════════════════════════ PILOTS ══════════════════════════ -->
  <section id="team">
    <div class="sec-header reveal">
      <span class="sec-index">// 01</span>
      <h2 class="sec-title">Pilotos</h2>
      <div class="sec-line"></div>
    </div>
    <div class="pilots-grid">
      <div class="pilot-card reveal">
        <div class="pilot-num">07</div>
        <div class="pilot-avatar-placeholder">A</div>
        <div class="pilot-info">
          <div class="pilot-name">Álex Moreno</div>
          <div class="pilot-role">// LEAD DRIVER</div>
          <div class="pilot-tags"><span class="tag">F1 2024</span><span class="tag">GT3</span><span class="tag">PRO</span></div>
        </div>
      </div>
      <div class="pilot-card reveal">
        <div class="pilot-num">14</div>
        <div class="pilot-avatar-placeholder">C</div>
        <div class="pilot-info">
          <div class="pilot-name">Carlos Vidal</div>
          <div class="pilot-role">// ENDURANCE SPECIALIST</div>
          <div class="pilot-tags"><span class="tag">LMP2</span><span class="tag">24H</span><span class="tag">PRO-AM</span></div>
        </div>
      </div>
      <div class="pilot-card reveal">
        <div class="pilot-num">33</div>
        <div class="pilot-avatar-placeholder">M</div>
        <div class="pilot-info">
          <div class="pilot-name">Marina López</div>
          <div class="pilot-role">// SPRINT DRIVER</div>
          <div class="pilot-tags"><span class="tag">GT4</span><span class="tag">ROOKIE</span></div>
        </div>
      </div>
      <div class="pilot-card reveal">
        <div class="pilot-num">99</div>
        <div class="pilot-avatar-placeholder">J</div>
        <div class="pilot-info">
          <div class="pilot-name">Jorge Ruiz</div>
          <div class="pilot-role">// OVAL SPECIALIST</div>
          <div class="pilot-tags"><span class="tag">iRACING</span><span class="tag">INDYCAR</span><span class="tag">A</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ══════════════════════════ RESULTS ══════════════════════════ -->
  <section id="results">
    <div class="sec-header reveal">
      <span class="sec-index">// 02</span>
      <h2 class="sec-title">Resultados</h2>
      <div class="sec-line"></div>
    </div>
    <div class="results-layout">
      <div class="big-stats">
        <div class="big-stat reveal">
          <div class="big-stat-num"><span class="count-up" data-target="89">0</span><span>%</span></div>
          <div class="big-stat-lbl">Tasa de podios</div>
          <div class="big-stat-sub">TEMPORADA 24</div>
        </div>
        <div class="big-stat reveal">
          <div class="big-stat-num">1:<span class="count-up" data-target="28">0</span>.4</div>
          <div class="big-stat-lbl">Mejor vuelta rápida</div>
          <div class="big-stat-sub">COTA [min]</div>
        </div>
        <div class="big-stat reveal">
          <div class="big-stat-num"><span class="count-up" data-target="156">0</span></div>
          <div class="big-stat-lbl">Carreras disputadas</div>
          <div class="big-stat-sub">ALL TIME</div>
        </div>
        <div class="big-stat reveal">
          <div class="big-stat-num"><span class="count-up" data-target="3">0</span></div>
          <div class="big-stat-lbl">Ligas activas</div>
          <div class="big-stat-sub">2024 SEASON</div>
        </div>
      </div>
      <div class="podium-panel reveal">
        <div class="podium-title">// DISTRIBUCIÓN DE POSICIONES — TEMP. 2024</div>
        <div class="podium-bars">
          <div class="p-bar-wrap"><div class="p-bar-label">P1</div><div class="p-bar p1" data-pos="P1" data-h="80" style="height:0"></div></div>
          <div class="p-bar-wrap"><div class="p-bar-label">P2</div><div class="p-bar p2" data-pos="P2" data-h="55" style="height:0"></div></div>
          <div class="p-bar-wrap"><div class="p-bar-label">P3</div><div class="p-bar p3" data-pos="P3" data-h="38" style="height:0"></div></div>
          <div class="p-bar-wrap"><div class="p-bar-label">P4-5</div><div class="p-bar" data-pos="" data-h="22" style="height:0;background:#222;"></div></div>
        </div>
        <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--gray);letter-spacing:.1em;">48 victorias — 33 segundos — 21 terceros puestos</p>
      </div>
      <div class="results-table-panel reveal">
        <div class="podium-title">// ÚLTIMAS CARRERAS</div>
        <table class="r-table">
          <thead><tr><th>POS</th><th>PILOTO</th><th>CIRCUITO</th><th>LIGA</th><th>Δ</th></tr></thead>
          <tbody>
            <tr><td><span class="pos-badge gold">1</span></td><td>A. Moreno</td><td>Spa-Francorchamps</td><td>ESL GT Pro</td><td class="delta-pos">▲4</td></tr>
            <tr><td><span class="pos-badge silver">2</span></td><td>C. Vidal</td><td>Le Mans</td><td>Endurance Cup</td><td class="delta-pos">▲1</td></tr>
            <tr><td><span class="pos-badge gold">1</span></td><td>A. Moreno</td><td>Monza</td><td>ESL F1 Open</td><td class="delta-pos">▲3</td></tr>
            <tr><td><span class="pos-badge bronze">3</span></td><td>M. López</td><td>Brands Hatch</td><td>GT4 Challenge</td><td class="delta-neg">▼1</td></tr>
            <tr><td><span class="pos-badge silver">2</span></td><td>J. Ruiz</td><td>Indianapolis</td><td>iRacing Open</td><td class="delta-pos">▲6</td></tr>
            <tr><td><span class="pos-badge gold">1</span></td><td>C. Vidal</td><td>Nürburgring 24H</td><td>Endurance Cup</td><td class="delta-pos">▲2</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ══════════════════════════ SPONSORS ══════════════════════════ -->
  <section id="sponsors">
    <div class="sec-header reveal">
      <span class="sec-index">// 03</span>
      <h2 class="sec-title">Patrocinadores</h2>
      <div class="sec-line"></div>
    </div>
    <div class="sponsors-strip">
      <!-- Para usar tu logo: cambia src="ruta/a/tu/logo.png" -->
      <a href="#" class="sponsor-card reveal">
        <img class="sponsor-logo-img" src="" alt="Fanatec" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <span class="sponsor-logo-text">FANATEC</span>
        <div class="sponsor-tier gold-tier">// GOLD SPONSOR</div>
      </a>
      <a href="#" class="sponsor-card reveal">
        <img class="sponsor-logo-img" src="" alt="Logitech" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <span class="sponsor-logo-text">LOGITECH</span>
        <div class="sponsor-tier gold-tier">// GOLD SPONSOR</div>
      </a>
      <a href="#" class="sponsor-card reveal">
        <img class="sponsor-logo-img" src="" alt="SimLab" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <span class="sponsor-logo-text">SIMLAB</span>
        <div class="sponsor-tier silver-tier">// SILVER SPONSOR</div>
      </a>
      <a href="#" class="sponsor-card reveal">
        <img class="sponsor-logo-img" src="" alt="MOZA" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <span class="sponsor-logo-text">MOZA</span>
        <div class="sponsor-tier silver-tier">// SILVER SPONSOR</div>
      </a>
      <a href="#" class="sponsor-card reveal">
        <img class="sponsor-logo-img" src="" alt="Heusinkveld" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <span class="sponsor-logo-text">HEUSINKVELD</span>
        <div class="sponsor-tier bronze-tier">// BRONZE SPONSOR</div>
      </a>
    </div>
    <div class="sponsor-cta-row reveal">
      <div class="sponsor-cta-text">
        <strong>¿Quieres patrocinar a Enosis?</strong><br>
        Únete a nuestro equipo y lleva tu marca al podio.
      </div>
      <a href="mailto:sponsors@enosis.gg" class="btn-primary">Contactar</a>
    </div>
  </section>

  <!-- ══════════════════════════ FOOTER ══════════════════════════ -->

    @include('layouts.partials.footer')

  <script>
    /* ── Cursor ── */
    const cursor = document.getElementById('cursor');
    const ring   = document.getElementById('cursorRing');
    let mx=0,my=0,rx=0,ry=0;
    document.addEventListener('mousemove', e => {
      mx=e.clientX; my=e.clientY;
      cursor.style.left=mx+'px'; cursor.style.top=my+'px';
    });
    (function loop(){ rx+=(mx-rx)*.12; ry+=(my-ry)*.12; ring.style.left=rx+'px'; ring.style.top=ry+'px'; requestAnimationFrame(loop); })();
    document.querySelectorAll('a,button,.pilot-card,.sponsor-card').forEach(el=>{
      el.addEventListener('mouseenter',()=>{ cursor.style.width='20px'; cursor.style.height='20px'; ring.style.width='56px'; ring.style.height='56px'; ring.style.borderColor='#fff'; });
      el.addEventListener('mouseleave',()=>{ cursor.style.width='10px'; cursor.style.height='10px'; ring.style.width='36px'; ring.style.height='36px'; ring.style.borderColor='var(--red)'; });
    });

    /* ── Speed lines ── */
    const sl = document.getElementById('speedLines');
    for(let i=0;i<14;i++){
      const s=document.createElement('span');
      s.style.cssText=`top:${Math.random()*100}%;width:${80+Math.random()*220}px;animation-duration:${2.5+Math.random()*3}s;animation-delay:${Math.random()*5}s;`;
      sl.appendChild(s);
    }

    /* ── GSAP ── */
    gsap.registerPlugin(ScrollTrigger);

    gsap.to('.hero-label',{opacity:1,y:0,duration:.7,delay:.3,ease:'power3.out'});
    gsap.to('.hero-title',{opacity:1,y:0,duration:.9,delay:.5,ease:'power3.out'});
    gsap.to('.hero-sub',  {opacity:1,y:0,duration:.8,delay:.75,ease:'power3.out'});
    gsap.to('.hero-ctas', {opacity:1,y:0,duration:.7,delay:.95,ease:'power3.out'});

    gsap.to('.hero-bg-text',{y:-120,ease:'none',scrollTrigger:{trigger:'#hero',start:'top top',end:'bottom top',scrub:true}});

    document.querySelectorAll('.reveal').forEach(el=>{
      gsap.to(el,{opacity:1,y:0,duration:.75,ease:'power3.out',scrollTrigger:{trigger:el,start:'top 88%',once:true}});
    });

    document.querySelectorAll('.p-bar').forEach(bar=>{
      const h=parseInt(bar.dataset.h);
      ScrollTrigger.create({trigger:bar,start:'top 90%',once:true,onEnter:()=>gsap.to(bar,{height:h+'px',duration:1,ease:'power3.out'})});
    });

    function countUp(el){
      const target=parseInt(el.dataset.target); let v=0;
      const step=target/(1800/16);
      const t=setInterval(()=>{ v=Math.min(v+step,target); el.textContent=Math.floor(v); if(v>=target)clearInterval(t); },16);
    }
    document.querySelectorAll('.count-up').forEach(el=>{
      ScrollTrigger.create({trigger:el,start:'top 90%',once:true,onEnter:()=>countUp(el)});
    });

    /* ── Telemetry ── */
    const lapEl=document.getElementById('lapTime');
    const rpmEl=document.getElementById('rpmVal');
    setInterval(()=>{
      const ms=Math.floor(Math.random()*999).toString().padStart(3,'0');
      const sec=(32+Math.floor(Math.random()*10)).toString().padStart(2,'0');
      lapEl.textContent=`1:${sec}.${ms}`;
      rpmEl.textContent=(10000+Math.floor(Math.random()*4000)).toString();
    },400);
  </script>
</body>
</html>
