<?php
$active   = 'basic-3';
$domain   = $_POST['domain']   ?? '';
$type     = $_POST['type']     ?? 'A';
$output   = '';
$error    = '';
$executed = false;

function safe($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$allowed_types = ['A', 'MX', 'NS', 'TXT', 'CNAME', 'AAAA'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $domain !== '') {
    $executed = true;

    // VULNERABLE: $domain tidak disanitasi — bisa injection
    // $type di-whitelist tapi masih bisa dimanipulasi jika validasi lemah
    $record_type = in_array(strtoupper($type), $allowed_types) ? strtoupper($type) : 'A';

    // Injection point ada di $domain
    $cmd    = "nslookup -type={$record_type} {$domain} 2>&1";
    $output = shell_exec($cmd);

    if ($output === null || trim($output) === '') {
        $error = "Tidak ada respons dari server DNS.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Basic 3: Multi-Field CMDi — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Basic 3: CMDi via Multiple Fields</span></div>
    <h1>CMDi via Multiple Input Fields <span class="tag g">BASIC 3</span></h1>
    <p class="phdr-desc">Aplikasi DNS lookup dengan dua field input. Satu field divalidasi, satu lainnya tidak. Pelajari cara mengidentifikasi field mana yang rentan ketika aplikasi memiliki beberapa parameter.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Identifikasi field mana yang rentan: <code class="ic">domain</code> atau <code class="ic">type</code></span></li>
      <li><div class="obj-n">2</div><span>Injeksi pada field domain menggunakan separator yang sesuai</span></li>
      <li><div class="obj-n">3</div><span>Eksekusi <code class="ic">id && cat /var/private/flag.txt</code> untuk mendapatkan flag</span></li>
      <li><div class="obj-n">4</div><span>Pahami mengapa validasi satu field tidak cukup jika field lain dibiarkan tanpa sanitasi</span></li>
    </ul>
  </div>

  <div class="box">
    <div class="box-t">Vulnerability Context</div>
    <div class="qbox"><div class="ql">Vulnerable PHP Code — Dua field input</div><span class="val">$domain</span>      = <span class="val">$_POST</span>[<span class="str">'domain'</span>];   <span class="cm">// tidak ada sanitasi ← VULNERABLE</span>
<span class="val">$type</span>        = <span class="val">$_POST</span>[<span class="str">'type'</span>];
<span class="val">$record_type</span> = <span class="at">in_array</span>(<span class="val">$type</span>, <span class="val">$allowed</span>) ? <span class="val">$type</span> : <span class="str">'A'</span>; <span class="cm">// divalidasi</span>

<span class="val">$cmd</span> = <span class="str">"nslookup -type={$record_type} {$domain} 2>&1"</span>;
<span class="at">shell_exec</span>(<span class="val">$cmd</span>);</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px">
      <div style="background:var(--gbg);border:1px solid var(--gbdr);border-radius:var(--r);padding:12px 14px">
        <div style="font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--green);font-family:var(--mono);margin-bottom:6px">Field: type</div>
        <p style="font-size:.8rem;color:var(--t2)">Divalidasi dengan whitelist — hanya nilai <code class="ic">A, MX, NS, TXT, CNAME, AAAA</code> yang diterima. Injection di sini tidak bisa dilakukan.</p>
      </div>
      <div style="background:var(--rbg);border:1px solid var(--rbdr);border-radius:var(--r);padding:12px 14px">
        <div style="font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--red);font-family:var(--mono);margin-bottom:6px">Field: domain</div>
        <p style="font-size:.8rem;color:var(--t2)">Tidak ada validasi atau sanitasi — langsung dimasukkan ke dalam command string. Injection dapat dilakukan di sini.</p>
      </div>
    </div>
  </div>

  <div class="box">
    <div class="box-t">DNS Lookup Tool</div>
    <form method="POST" action="/basic-3/">
      <div style="display:grid;grid-template-columns:1fr 160px;gap:12px;align-items:end">
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Domain <span style="color:var(--red);font-size:.65rem">(VULNERABLE)</span></label>
          <input class="fi" type="text" name="domain"
            value="<?= safe($domain) ?>"
            placeholder="google.com atau google.com; id">
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Record Type <span style="color:var(--green);font-size:.65rem">(AMAN)</span></label>
          <select class="fi" name="type">
            <?php foreach (['A','MX','NS','TXT','CNAME','AAAA'] as $t): ?>
            <option value="<?= $t ?>"<?= $type===$t?' selected':'' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="margin-top:14px;display:flex;gap:10px">
        <button type="submit" class="btn btn-r">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Lookup
        </button>
        <?php if($executed): ?><a href="/basic-3/" class="btn btn-g">Reset</a><?php endif; ?>
      </div>
    </form>
  </div>

  <?php if ($executed): ?>

  <div class="box">
    <div class="box-t">Command Executed</div>
    <div class="qbox" style="margin-bottom:0;color:var(--orange)"><div class="ql">Shell Command</div>nslookup -type=<?= safe($record_type ?? 'A') ?> <?= safe($domain) ?> 2&gt;&amp;1</div>
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
    <div class="box-t">Hints</div>
    <details class="hint">
      <summary>Hint 1 &mdash; Identifikasi field vulnerable</summary>
      <div class="hint-body">Coba injection di field <strong>type</strong> — pilih dari dropdown, tidak bisa diinjeksi. Sekarang coba di field <strong>domain</strong>: masukkan <code class="ic">google.com; id</code> dan perhatikan hasilnya.</div>
    </details>
    <details class="hint">
      <summary>Hint 2 &mdash; Payload sederhana</summary>
      <div class="hint-body"><code class="ic">google.com; id</code><br><code class="ic">google.com; whoami</code><br><code class="ic">google.com; ls /var/private</code></div>
    </details>
    <details class="hint">
      <summary>Hint 3 &mdash; Baca flag</summary>
      <div class="hint-body"><code class="ic">google.com; cat /var/private/flag.txt</code><br>Atau jika <code class="ic">;</code> tidak bekerja: <code class="ic">google.com && cat /var/private/flag.txt</code></div>
    </details>
    <details class="hint">
      <summary>Hint 4 &mdash; Pelajaran penting</summary>
      <div class="hint-body">Memvalidasi satu field tidak membuat aplikasi aman jika field lain masih raw. Setiap parameter yang berakhir di system command harus di-sanitasi — tidak ada pengecualian.</div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
