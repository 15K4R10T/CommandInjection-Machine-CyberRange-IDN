<?php $active = 'home'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Command Injection Lab — ID-Networkers</title>
<?php include 'includes/shared_css.php'; ?>
<style>
.hero{background:var(--surface);border-bottom:1px solid var(--bd)}
.hero-in{max-width:1160px;margin:0 auto;padding:60px 40px 52px;display:grid;grid-template-columns:1fr 210px;gap:56px;align-items:center;position:relative}
.hero-in::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 50% 100% at 0 50%,rgba(230,57,70,.05),transparent 65%);pointer-events:none}
.hero-eye{display:inline-flex;align-items:center;gap:8px;font-size:.66rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--red);background:var(--rbg);border:1px solid var(--rbdr);padding:4px 12px;border-radius:20px;margin-bottom:20px}
.hero-eye i{width:6px;height:6px;border-radius:50%;background:var(--red);animation:blink 2s infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.hero h1{font-size:2.5rem;font-weight:800;line-height:1.15;letter-spacing:-.025em;color:var(--t1);margin-bottom:16px}
.hero h1 b{color:var(--red)}
.hero-sub{font-size:.9rem;color:var(--t2);max-width:520px;line-height:1.8;margin-bottom:24px}
.hero-note{display:inline-flex;align-items:center;gap:10px;font-size:.72rem;font-family:var(--mono);color:var(--t3);border:1px solid var(--bd);border-radius:var(--r);padding:8px 16px;background:var(--bg)}
.dot-r{width:7px;height:7px;border-radius:50%;background:var(--red);flex-shrink:0;animation:blink 2s infinite}
.hero-stats{display:flex;flex-direction:column;gap:10px}
.stat{background:var(--card);border:1px solid var(--bd);border-radius:var(--r2);padding:16px 20px;text-align:center;transition:border-color .15s}
.stat:hover{border-color:var(--red)}
.stat-n{font-size:2rem;font-weight:800;color:var(--red);font-family:var(--mono);line-height:1}
.stat-l{font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);margin-top:5px}
.main{max-width:1160px;margin:0 auto;padding:44px 40px 72px}
.sec{margin-bottom:44px}
.sec-head{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.sec-head::before{content:'';width:3px;height:16px;background:var(--red);border-radius:2px;flex-shrink:0}
.sec-head h2{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--t2)}
.about{background:var(--card);border:1px solid var(--bd);border-left:3px solid var(--red);border-radius:var(--r2);padding:22px 26px}
.about p{font-size:.88rem;color:var(--t2);line-height:1.85}
/* FLOW */
.flow{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.flow-step{background:var(--card);border:1px solid var(--bd);border-radius:var(--r2);padding:16px;text-align:center;position:relative}
.flow-step:not(:last-child)::after{content:'→';position:absolute;right:-14px;top:50%;transform:translateY(-50%);color:var(--t3);font-size:1rem;z-index:1}
.flow-num{font-size:.62rem;font-weight:700;letter-spacing:.1em;font-family:var(--mono);color:var(--red);margin-bottom:6px}
.flow-title{font-size:.84rem;font-weight:700;color:var(--t1);margin-bottom:4px}
.flow-desc{font-size:.73rem;color:var(--t2);line-height:1.5}
/* MODULES */
.mod-section{margin-bottom:26px}
.mod-label{font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--t3);margin-bottom:12px;font-family:var(--mono)}
.mods{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.mod{display:block;color:inherit;background:var(--card);border:1px solid var(--bd);border-radius:var(--r2);overflow:hidden;transition:transform .15s,border-color .15s,box-shadow .15s}
.mod:hover{transform:translateY(-3px);border-color:var(--bd2);box-shadow:0 14px 40px rgba(0,0,0,.45)}
.mod-line{height:3px}
.mod-line.g{background:var(--green)}.mod-line.o{background:var(--orange)}
.mod-line.r{background:var(--red)}.mod-line.p{background:var(--purple)}
.mod-body{padding:20px}
.mod-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.mod-ico{width:36px;height:36px;border-radius:var(--r);display:flex;align-items:center;justify-content:center}
.mod-ico.g{background:var(--gbg);color:var(--green)}
.mod-ico.o{background:var(--obg);color:var(--orange)}
.mod-ico.r{background:var(--rbg);color:var(--red)}
.mod-ico.p{background:var(--pbg);color:var(--purple)}
.mod-ico svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.mod h3{font-size:.92rem;font-weight:700;color:var(--t1);margin-bottom:7px}
.mod-desc{font-size:.81rem;color:var(--t2);line-height:1.65;margin-bottom:12px}
.mod-list{list-style:none;display:flex;flex-direction:column;gap:4px;margin-bottom:18px}
.mod-list li{font-size:.75rem;color:var(--t3);font-family:var(--mono);padding-left:13px;position:relative}
.mod-list li::before{content:'›';position:absolute;left:0;color:var(--red)}
.mod-foot{display:flex;align-items:center;justify-content:space-between;padding-top:13px;border-top:1px solid var(--bd);font-size:.77rem;font-weight:600;color:var(--t3);transition:color .15s}
.mod:hover .mod-foot{color:var(--red)}
.mod-foot svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
/* SEPARATOR CHEATSHEET on dashboard */
.sep-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.sep-card{background:var(--card);border:1px solid var(--bd);border-radius:var(--r2);padding:14px 16px}
.sep-sym{font-family:var(--mono);font-size:1.1rem;font-weight:700;color:var(--orange);margin-bottom:5px}
.sep-name{font-size:.8rem;font-weight:700;color:var(--t1);margin-bottom:4px}
.sep-desc{font-size:.74rem;color:var(--t2);line-height:1.6}
@media(max-width:900px){
  .hero-in,.mods,.flow,.sep-grid{grid-template-columns:1fr}
  .hero-stats{flex-direction:row}.stat{flex:1}
  .nav,.main,footer{padding-left:20px;padding-right:20px}
  .hero-in{padding:40px 20px}
  .flow-step:not(:last-child)::after{display:none}
}
</style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<div class="hero">
  <div class="hero-in">
    <div>
      <div class="hero-eye"><i></i>Vulnerability Research</div>
      <h1>Command Injection<br><b>Lab Environment</b></h1>
      <p class="hero-sub">Lingkungan praktik OS Command Injection terstruktur untuk keperluan edukasi keamanan siber. Enam modul mencakup teknik dari basic injection, blind command injection, filter bypass, hingga WAF evasion dengan berbagai command separator.</p>
      <div class="hero-note">
        <span class="dot-r"></span>
        FOR EDUCATIONAL USE ONLY &mdash; Gunakan hanya di environment lab terisolasi
      </div>
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="stat-n">6</div><div class="stat-l">Modules</div></div>
      <div class="stat"><div class="stat-n">6</div><div class="stat-l">Challenges</div></div>
      <div class="stat"><div class="stat-n">6</div><div class="stat-l">Flags</div></div>
    </div>
  </div>
</div>

<div class="main">

  <div class="sec">
    <div class="sec-head"><h2>Tentang Lab</h2></div>
    <div class="about">
      <p>OS Command Injection terjadi ketika aplikasi meneruskan input pengguna ke shell sistem operasi tanpa sanitasi yang memadai. Attacker dapat menyisipkan command separator untuk mengeksekusi perintah arbitrer di server — mulai dari pembacaan file sensitif, enumerasi sistem, hingga pengambilalihan penuh atas server. Lab ini menyimulasikan skenario aplikasi nyata seperti network diagnostic tools, domain lookup, dan file processing utilities yang rentan terhadap serangan ini.</p>
    </div>
  </div>

  <div class="sec">
    <div class="sec-head"><h2>Attack Flow</h2></div>
    <div class="flow">
      <div class="flow-step"><div class="flow-num">01</div><div class="flow-title">Identify</div><div class="flow-desc">Temukan fitur yang menggunakan input untuk menjalankan system command</div></div>
      <div class="flow-step"><div class="flow-num">02</div><div class="flow-title">Probe</div><div class="flow-desc">Uji separator seperti <code class="ic">;</code> <code class="ic">&&</code> <code class="ic">|</code> untuk mengeksekusi command tambahan</div></div>
      <div class="flow-step"><div class="flow-num">03</div><div class="flow-title">Execute</div><div class="flow-desc">Jalankan command seperti <code class="ic">id</code>, <code class="ic">whoami</code>, <code class="ic">cat /etc/passwd</code></div></div>
      <div class="flow-step"><div class="flow-num">04</div><div class="flow-title">Escalate</div><div class="flow-desc">Baca file sensitif, buat reverse shell, atau pivot ke sistem lain</div></div>
    </div>
  </div>

  <div class="sec">
    <div class="sec-head"><h2>Command Separators</h2></div>
    <div class="sep-grid">
      <div class="sep-card"><div class="sep-sym">;</div><div class="sep-name">Semicolon</div><div class="sep-desc">Jalankan command berikutnya tanpa memedulikan exit code sebelumnya. Bekerja di bash/sh.</div></div>
      <div class="sep-card"><div class="sep-sym">&&</div><div class="sep-name">AND Operator</div><div class="sep-desc">Jalankan command berikutnya hanya jika command sebelumnya berhasil (exit code 0).</div></div>
      <div class="sep-card"><div class="sep-sym">||</div><div class="sep-name">OR Operator</div><div class="sep-desc">Jalankan command berikutnya hanya jika command sebelumnya gagal (exit code bukan 0).</div></div>
      <div class="sep-card"><div class="sep-sym">|</div><div class="sep-name">Pipe</div><div class="sep-desc">Kirim output command pertama sebagai input command kedua.</div></div>
      <div class="sep-card"><div class="sep-sym">`cmd`</div><div class="sep-name">Backtick</div><div class="sep-desc">Command substitution — output disubstitusi ke dalam string. Berguna di dalam argumen.</div></div>
      <div class="sep-card"><div class="sep-sym">$(cmd)</div><div class="sep-name">Subshell</div><div class="sep-desc">Command substitution modern. Bisa nested dan lebih readable dari backtick.</div></div>
    </div>
  </div>

  <div class="sec">
    <div class="sec-head"><h2>Lab Modules</h2></div>

    <div class="mod-section">
      <div class="mod-label">Basic Series</div>
      <div class="mods">

        <a href="/basic-1/" class="mod">
          <div class="mod-line g"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico g"><svg viewBox="0 0 24 24"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg></div>
              <span class="tag g">BASIC 1</span>
            </div>
            <h3>Basic Command Injection</h3>
            <p class="mod-desc">Tool ping diagnostik menggunakan input host langsung di <code class="ic">shell_exec()</code> tanpa sanitasi apapun.</p>
            <ul class="mod-list"><li>shell_exec() tanpa filter</li><li>Command separator injection</li><li>Eksekusi id, whoami, uname</li></ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/basic-2/" class="mod">
          <div class="mod-line o"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico o"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
              <span class="tag o">BASIC 2</span>
            </div>
            <h3>Blind Command Injection</h3>
            <p class="mod-desc">Output command tidak ditampilkan ke halaman. Gunakan teknik time-based dan out-of-band untuk membuktikan eksekusi.</p>
            <ul class="mod-list"><li>Time-based detection (sleep)</li><li>Output redirection ke file</li><li>Out-of-band via DNS/HTTP</li></ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/basic-3/" class="mod">
          <div class="mod-line g"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico g"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
              <span class="tag g">BASIC 3</span>
            </div>
            <h3>CMDi via Multiple Fields</h3>
            <p class="mod-desc">Aplikasi DNS lookup dengan dua field input. Injection dapat terjadi di field manapun yang diteruskan ke sistem.</p>
            <ul class="mod-list"><li>Multi-parameter injection</li><li>Injection di hidden/secondary fields</li><li>Command chaining via berbagai field</li></ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

      </div>
    </div>

    <div class="mod-section">
      <div class="mod-label">Advanced Series</div>
      <div class="mods">

        <a href="/advanced-1/" class="mod">
          <div class="mod-line o"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico o"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
              <span class="tag o">ADV 1</span>
            </div>
            <h3>CMDi + Filter Bypass</h3>
            <p class="mod-desc">Aplikasi memblokir keyword dan separator tertentu. Gunakan encoding, case variation, dan teknik obfuscation untuk bypass.</p>
            <ul class="mod-list"><li>Blacklist keyword evasion</li><li>$IFS sebagai pengganti spasi</li><li>Wildcard dan variable expansion</li></ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/advanced-2/" class="mod">
          <div class="mod-line r"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico r"><svg viewBox="0 0 24 24"><path d="M1 6v16l7-4 8 4 7-4V2l-7 4-8-4-7 4z"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg></div>
              <span class="tag r">ADV 2</span>
            </div>
            <h3>Blind CMDi + OOB</h3>
            <p class="mod-desc">Tidak ada output dan tidak ada delay yang terlihat. Gunakan teknik Out-of-Band (DNS/HTTP callback) untuk mengekstrak data.</p>
            <ul class="mod-list"><li>DNS exfiltration via nslookup</li><li>HTTP callback exfiltration</li><li>Data encoding dalam subdomain</li></ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/advanced-3/" class="mod">
          <div class="mod-line p"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico p"><svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
              <span class="tag p">ADV 3</span>
            </div>
            <h3>CMDi + WAF Evasion</h3>
            <p class="mod-desc">WAF pattern-based aktif memblokir karakter dan keyword umum. Gunakan teknik evasion tingkat lanjut untuk bypass.</p>
            <ul class="mod-list"><li>URL double encoding</li><li>Command obfuscation lanjutan</li><li>Environment variable abuse</li></ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

      </div>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
