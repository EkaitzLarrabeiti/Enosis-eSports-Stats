<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inicio | Enosis eSports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
  <style>
    :root {
      --red:     #e8000d;
      --red-dim: #7a0007;
      --white:   #f0ece4;
      --gray:    #888580;
      --dark:    #0a0a0a;
      --carbon:  #111111;
      --panel:   #141414;
      --border:  #2a2a2a;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
      background: var(--dark);
      color: var(--white);
      font-family: 'Barlow Condensed', sans-serif;
      overflow-x: hidden;
    }

    /* ─────────────────────────────────────────────
       HERO
    ───────────────────────────────────────────── */
    #hero {
      position: relative;
      min-height: 92vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 0 6vw;
      overflow: hidden;
    }

    /* Imagen de fondo: reemplaza la URL por tu imagen */
    .hero-bg-img {
      position: absolute;
      inset: 0;
      background-image: url('img/silverstone1.png');
      background-size: cover;
      background-position: center;
      filter: blur(4px) brightness(0.22) saturate(0.5);
      transform: scale(1.06);
      z-index: 0;
    }

    .hero-grid {
      position: absolute; inset: 0;
      z-index: 1;
      background-image:
        linear-gradient(rgba(232,0,13,.07) 1px, transparent 1px),
        linear-gradient(90deg, rgba(232,0,13,.07) 1px, transparent 1px);
      background-size: 60px 60px;
      animation: gridShift 20s linear infinite;
    }
    @keyframes gridShift {
      from { background-position: 0 0; }
      to   { background-position: 60px 60px; }
    }

    .speed-lines {
      position: absolute; inset: 0;
      overflow: hidden;
      pointer-events: none;
      z-index: 2;
    }
    .speed-lines span {
      position: absolute;
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--red), transparent);
      opacity: 0;
      animation: speedLine 3s ease-in-out infinite;
    }
    @keyframes speedLine {
      0%   { transform: translateX(-100%); opacity: 0; }
      10%  { opacity: .6; }
      90%  { opacity: .6; }
      100% { transform: translateX(200vw); opacity: 0; }
    }

    .hero-bg-text {
      position: absolute;
      right: -2vw; top: 50%;
      transform: translateY(-50%);
      font-family: 'Orbitron', monospace;
      font-size: clamp(120px, 22vw, 320px);
      font-weight: 900;
      color: transparent;
      -webkit-text-stroke: 1px rgba(232,0,13,.15);
      letter-spacing: -0.04em;
      white-space: nowrap;
      user-select: none;
      pointer-events: none;
      z-index: 2;
    }

    .telem-bar {
      position: absolute;
      top: 28px; left: 6vw; right: 6vw;
      display: flex;
      gap: 32px;
      align-items: center;
      font-family: 'Share Tech Mono', monospace;
      font-size: 11px;
      color: var(--gray);
      border-bottom: 1px solid var(--border);
      padding-bottom: 12px;
      z-index: 3;
    }
    .telem-bar .live-dot {
      width: 7px; height: 7px;
      background: var(--red);
      border-radius: 50%;
      animation: pulse 1.2s ease-in-out infinite;
      flex-shrink: 0;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(232,0,13,.6); }
      50%       { opacity: .5; box-shadow: 0 0 0 6px rgba(232,0,13,0); }
    }
    .telem-val { color: var(--red); }

    .hero-content { position: relative; z-index: 3; }

    .hero-label {
      font-family: 'Share Tech Mono', monospace;
      font-size: 12px;
      color: var(--red);
      letter-spacing: .3em;
      text-transform: uppercase;
      margin-bottom: 16px;
      opacity: 0;
    }
    .hero-title {
      font-family: 'Orbitron', monospace;
      font-size: clamp(52px, 9vw, 130px);
      font-weight: 900;
      line-height: .9;
      letter-spacing: -.02em;
      text-transform: uppercase;
      opacity: 0;
    }
    .hero-title-logo {
      display: block;
      width: clamp(280px, 48vw, 760px);
      max-width: 100%;
      height: auto;
    }
    .hero-title .line2 { display: block; color: transparent; -webkit-text-stroke: 2px var(--white); }
    .hero-title .accent { color: var(--red); }

    .hero-sub {
      margin-top: 28px;
      font-size: clamp(14px, 1.8vw, 20px);
      font-weight: 300;
      color: var(--gray);
      max-width: 540px;
      line-height: 1.5;
      letter-spacing: .04em;
      opacity: 0;
    }
    .hero-ctas { margin-top: 44px; display: flex; gap: 16px; flex-wrap: wrap; opacity: 0; }

    .btn-primary {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 15px; font-weight: 700;
      letter-spacing: .2em; text-transform: uppercase; text-decoration: none;
      padding: 14px 40px;
      background: var(--red); color: #fff; border: none; cursor: pointer;
      clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%);
      transition: background .2s, transform .15s;
      position: relative; overflow: hidden; display: inline-block;
    }
    .btn-primary::after {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
      transform: translateX(-100%); transition: transform .4s;
    }
    .btn-primary:hover { background: #ff0a14; transform: translateY(-2px); }
    .btn-primary:hover::after { transform: translateX(100%); }

    .btn-secondary {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 15px; font-weight: 700;
      letter-spacing: .2em; text-transform: uppercase; text-decoration: none;
      padding: 13px 40px;
      background: transparent; color: var(--white);
      border: 1px solid var(--border); cursor: pointer;
      clip-path: polygon(12px 0, 100% 0, 100% 100%, 0 100%, 0 12px);
      transition: border-color .2s, color .2s, transform .15s;
      display: inline-block;
    }
    .btn-secondary:hover { border-color: var(--red); color: var(--red); transform: translateY(-2px); }

    .stat-strip {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      display: flex;
      border-top: 1px solid var(--border);
      background: rgba(10,10,10,.88);
      backdrop-filter: blur(6px);
      z-index: 3;
    }
    .stat-item { flex: 1; padding: 20px 6vw; border-right: 1px solid var(--border); }
    .stat-item:last-child { border-right: none; }
    .stat-num {
      font-family: 'Orbitron', monospace;
      font-size: clamp(28px, 4vw, 48px);
      font-weight: 900; color: var(--red); line-height: 1;
    }
    .stat-lbl {
      font-family: 'Share Tech Mono', monospace;
      font-size: 11px; color: var(--gray); letter-spacing: .18em; margin-top: 4px;
    }

    /* ─────────────────────────────────────────────
       SECTION COMMONS
    ───────────────────────────────────────────── */
    section { padding: 100px 6vw; }

    .sec-header { display: flex; align-items: baseline; gap: 20px; margin-bottom: 60px; }
    .sec-index { font-family: 'Share Tech Mono', monospace; font-size: 11px; color: var(--red); letter-spacing: .2em; }
    .sec-title { font-family: 'Orbitron', monospace; font-size: clamp(28px, 4vw, 52px); font-weight: 900; text-transform: uppercase; letter-spacing: -.01em; }
    .sec-line { flex: 1; height: 1px; background: linear-gradient(90deg, var(--red), transparent); margin-left: 20px; }

    /* ─────────────────────────────────────────────
       PILOTS
    ───────────────────────────────────────────── */
    #team { background: var(--carbon); }

    .pilots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 2px; }

    .pilot-card {
      position: relative; background: var(--panel); overflow: hidden;
      cursor: pointer; transition: transform .3s;
    }
    .pilot-card:hover { transform: translateY(-4px); z-index: 2; }
    .pilot-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: var(--red); transform: scaleX(0); transform-origin: left; transition: transform .35s;
    }
    .pilot-card:hover::before { transform: scaleX(1); }

    .pilot-num {
      position: absolute; top: 16px; right: 16px;
      font-family: 'Orbitron', monospace; font-size: 40px; font-weight: 900;
      color: rgba(232,0,13,.12); line-height: 1; transition: color .3s;
    }
    .pilot-card:hover .pilot-num { color: rgba(232,0,13,.28); }

    .pilot-avatar-placeholder {
      width: 100%; aspect-ratio: 1/1; background: var(--border);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Orbitron', monospace; font-size: 48px; font-weight: 900;
      color: var(--red-dim); transition: color .3s;
    }
    .pilot-card:hover .pilot-avatar-placeholder { color: var(--red); }

    .pilot-info { padding: 20px; border-top: 1px solid var(--border); }
    .pilot-name { font-family: 'Barlow Condensed', sans-serif; font-size: 22px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .pilot-role { font-family: 'Share Tech Mono', monospace; font-size: 11px; color: var(--red); letter-spacing: .18em; margin-top: 4px; }
    .pilot-tags { display: flex; gap: 6px; margin-top: 12px; flex-wrap: wrap; }
    .tag { font-family: 'Share Tech Mono', monospace; font-size: 9px; letter-spacing: .12em; padding: 3px 8px; border: 1px solid var(--border); color: var(--gray); }

    /* ─────────────────────────────────────────────
       RESULTS
    ───────────────────────────────────────────── */
    #results { background: var(--dark); }

    .results-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 2px; }
    @media (max-width: 768px) { .results-layout { grid-template-columns: 1fr; } }

    .big-stats { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(4, 1fr); gap: 2px; }
    @media (max-width: 600px) { .big-stats { grid-template-columns: repeat(2, 1fr); } }

    .big-stat {
      background: var(--panel); padding: 32px 28px;
      position: relative; overflow: hidden;
      border-left: 2px solid transparent; transition: border-color .3s;
    }
    .big-stat:hover { border-left-color: var(--red); }
    .big-stat-num { font-family: 'Orbitron', monospace; font-size: clamp(32px, 4vw, 52px); font-weight: 900; color: var(--white); line-height: 1; }
    .big-stat-num span { color: var(--red); }
    .big-stat-lbl { font-family: 'Share Tech Mono', monospace; font-size: 10px; letter-spacing: .18em; color: var(--gray); margin-top: 8px; text-transform: uppercase; }
    .big-stat-sub { position: absolute; bottom: 16px; right: 16px; font-family: 'Share Tech Mono', monospace; font-size: 9px; color: rgba(232,0,13,.4); letter-spacing: .1em; }

    .podium-panel { background: var(--panel); padding: 40px; position: relative; overflow: hidden; }
    .podium-panel::after { content: 'P1'; position: absolute; bottom: -10px; right: -10px; font-family: 'Orbitron', monospace; font-size: 120px; font-weight: 900; color: rgba(232,0,13,.05); line-height: 1; pointer-events: none; }

    .podium-title { font-family: 'Share Tech Mono', monospace; font-size: 11px; color: var(--red); letter-spacing: .25em; margin-bottom: 28px; }

    .podium-bars { display: flex; align-items: flex-end; gap: 8px; height: 160px; margin-bottom: 12px; }
    .p-bar-wrap { display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; }
    .p-bar-label { font-family: 'Share Tech Mono', monospace; font-size: 10px; color: var(--gray); letter-spacing: .1em; }
    .p-bar { width: 100%; background: var(--border); position: relative; }
    .p-bar.p1 { background: var(--red); }
    .p-bar.p2 { background: #555; }
    .p-bar.p3 { background: #333; }
    .p-bar::after { content: attr(data-pos); position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); font-family: 'Orbitron', monospace; font-size: 14px; font-weight: 900; color: rgba(255,255,255,.5); }

    .results-table-panel { background: var(--panel); padding: 40px; }
    .r-table { width: 100%; border-collapse: collapse; font-family: 'Share Tech Mono', monospace; font-size: 13px; }
    .r-table thead tr { border-bottom: 1px solid var(--red); }
    .r-table th { font-size: 10px; letter-spacing: .2em; color: var(--red); padding: 0 0 12px; text-align: left; }
    .r-table th:last-child, .r-table td:last-child { text-align: right; }
    .r-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    .r-table tbody tr:hover { background: rgba(232,0,13,.04); }
    .r-table td { padding: 14px 0; color: var(--white); }
    .pos-badge { display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; font-size: 11px; font-weight: 700; }
    .pos-badge.gold   { background: #c8960a; color: #000; }
    .pos-badge.silver { background: #888;    color: #000; }
    .pos-badge.bronze { background: #7a4a20; color: #fff; }
    .delta-pos { color: #4caf50; }
    .delta-neg { color: var(--red); }

    /* ─────────────────────────────────────────────
       SPONSORS
    ───────────────────────────────────────────── */
    #sponsors { background: var(--carbon); }

    .sponsors-strip { display: flex; gap: 2px; flex-wrap: wrap; }

    .sponsor-card {
      flex: 1; min-width: 160px; background: var(--panel); border: 1px solid var(--border);
      padding: 36px 28px; display: flex; flex-direction: column; align-items: center;
      justify-content: center; gap: 14px;
      transition: border-color .25s, transform .2s; cursor: pointer; text-decoration: none;
    }
    .sponsor-card:hover { border-color: var(--red); transform: translateY(-3px); }

    /* Logo imagen en escala de grises, a color en hover */
    .sponsor-logo-img {
      height: 44px; max-width: 130px; object-fit: contain;
      filter: grayscale(1) brightness(0.75); transition: filter .3s;
    }
    .sponsor-card:hover .sponsor-logo-img { filter: grayscale(0) brightness(1); }

    /* Fallback texto cuando no hay imagen */
    .sponsor-logo-text {
      font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700;
      letter-spacing: .1em; color: var(--gray); transition: color .25s;
    }
    .sponsor-card:hover .sponsor-logo-text { color: var(--white); }

    .sponsor-tier { font-family: 'Share Tech Mono', monospace; font-size: 9px; letter-spacing: .2em; text-transform: uppercase; }
    .sponsor-tier.gold-tier   { color: #c8960a; }
    .sponsor-tier.silver-tier { color: #666; }
    .sponsor-tier.bronze-tier { color: #7a4a20; }

    .sponsor-cta-row {
      margin-top: 48px; padding: 32px 40px; background: var(--panel); border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;
    }
    .sponsor-cta-text { font-family: 'Barlow Condensed', sans-serif; font-size: 22px; font-weight: 600; letter-spacing: .04em; color: var(--gray); }
    .sponsor-cta-text strong { color: var(--white); }

    /* ─────────────────────────────────────────────
       FOOTER
    ───────────────────────────────────────────── */
    footer {
      background: #080808; border-top: 1px solid var(--border);
      padding: 32px 6vw; display: flex; align-items: center;
      justify-content: space-between; flex-wrap: wrap; gap: 16px;
    }
    .footer-brand { font-family: 'Orbitron', monospace; font-size: 14px; font-weight: 900; letter-spacing: .15em; }
    .footer-brand span { color: var(--red); }
    .footer-meta { font-family: 'Share Tech Mono', monospace; font-size: 10px; color: var(--gray); letter-spacing: .15em; }
    .footer-socials { display: flex; gap: 20px; }
    .footer-socials a { font-family: 'Share Tech Mono', monospace; font-size: 10px; color: var(--gray); letter-spacing: .15em; text-decoration: none; transition: color .2s; }
    .footer-socials a:hover { color: var(--red); }

    /* ─────────────────────────────────────────────
       SCROLL REVEAL
    ───────────────────────────────────────────── */
    .reveal { opacity: 0; transform: translateY(30px); }

    /* ─────────────────────────────────────────────
       CURSOR
    ───────────────────────────────────────────── */
    .cursor { position: fixed; width: 10px; height: 10px; background: var(--red); border-radius: 50%; pointer-events: none; z-index: 10000; transform: translate(-50%,-50%); transition: width .2s, height .2s; mix-blend-mode: difference; }
    .cursor-ring { position: fixed; width: 36px; height: 36px; border: 1px solid var(--red); border-radius: 50%; pointer-events: none; z-index: 10000; transform: translate(-50%,-50%); opacity: .5; transition: width .2s, height .2s, border-color .2s; }
    @media (max-width: 600px) { .cursor, .cursor-ring { display: none; } .telem-bar { display: none; } }
  </style>
</head>
<body>

  @include('layouts.partials.header')

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- ══════════════════════════ HERO ══════════════════════════ -->
  <section id="hero">
    <div class="hero-bg-img"></div>
    <div class="hero-grid"></div>
    <div class="speed-lines" id="speedLines"></div>
    <div class="hero-bg-text">ENOSIS</div>

    <div class="telem-bar">
      <span class="live-dot"></span>
      <span>SYS_ONLINE</span>
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
  <footer>
    <div class="footer-brand">ENOSIS <span>eSports</span></div>
    <div class="footer-meta">© 2024 ENOSIS ESPORTS — ALL RIGHTS RESERVED</div>
    <div class="footer-socials">
      <a href="#">DISCORD</a>
      <a href="#">TWITTER</a>
      <a href="#">TWITCH</a>
      <a href="#">YOUTUBE</a>
    </div>
  </footer>

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
