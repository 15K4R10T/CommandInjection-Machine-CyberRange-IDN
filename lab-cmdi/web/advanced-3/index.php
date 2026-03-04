<?php
$active   = 'advanced-3';
$host     = $_POST['host'] ?? '';
$output   = '';
$blocked  = false;
$executed = false;
$waf_reason = '';

function safe($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/**
 * Simulasi WAF — pattern-based detection yang lebih agresif
 */
function wafCheck(string $input): array {
    $patterns = [
        '/;/'                          => 'Semicolon separator',
        '/&&/'                         => 'AND operator',
        '/\|\|/'                       => 'OR operator',
        '/\|[^|]/'                     => 'Pipe character',
        '/`/'                          => 'Backtick',
        '/\$\(/'                       => 'Subshell $()',
        '/\bcat\b/i'                   => 'Keyword: cat',
        '/\bwhoami\b/i'                => 'Keyword: whoami',
        '/\bls\b/i'                    => 'Keyword: ls',
        '/\bid\b/i'                    => 'Keyword: id',
        '/\/etc\//'                    => 'Path: /etc/',
        '/\/var\//'                    => 'Path: /var/',
        '/%[0-9a-f]{2}/i'             => 'URL encoding detected',
        '/\$IFS/'                      => 'IFS variable',
        '/\b(sleep|wget|curl|nc)\b/i'  => 'Dangerous command',
    ];

    foreach ($patterns as $pattern => $reason) {
        if (preg_match($pattern, $input)) {
            return ['blocked' => true, 'reason' => $reason];
        }
    }
    return ['blocked' => false, 'reason' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $host !== '') {
    $waf_result = wafCheck($host);

    if ($waf_result['blocked']) {
        $blocked    = true;
        $waf_reason = $waf_result['reason'];
    } else {
        $executed = true;
        $cmd      = "ping -c 2 " . $host . " 2>&1";
        $output   = shell_exec($cmd) ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advanced 3: CMDi WAF Evasion — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Advanced 3: CMDi + WAF Evasion</span></div>
    <h1>CMDi + WAF Evasion <span class="tag p">ADVANCED 3</span></h1>
    <p class="phdr-desc">Web Application Firewall (WAF) aktif dengan 15 pattern detection yang memblokir karakter, keyword, path, dan encoding berbahaya. Gunakan teknik obfuscation tingkat lanjut untuk melewati semua rule sekaligus.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Analisis semua 15 rule WAF dan identifikasi celah atau teknik yang belum dicakup</span></li>
      <li><div class="obj-n">2</div><span>Bypass WAF untuk mengeksekusi command <code class="ic">id</code> tanpa memicu rule manapun</span></li>
      <li><div class="obj-n">3</div><span>Baca <code class="ic">/var/private/flag.txt</code> menggunakan teknik yang melewati seluruh filter</span></li>
      <li><div class="obj-n">4</div><span>Pahami mengapa regex-based WAF tidak bisa sepenuhnya memproteksi dari injection</span></li>
    </ul>
  </div>

  <!-- WAF Rules -->
  <div class="box">
    <div class="box-t">Active WAF Rules &mdash; 15 Patterns</div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>#</th><th>Pattern</th><th>Keterangan</th></tr></thead>
        <tbody>
          <?php
          $rules = [
            [1, ';',                'Semicolon separator'],
            [2, '&&',               'AND operator'],
            [3, '||',               'OR operator'],
            [4, '| (single)',       'Pipe character'],
            [5, '`backtick`',       'Command substitution'],
            [6, '$()',              'Subshell expression'],
            [7, 'cat (keyword)',    'Read file command'],
            [8, 'whoami (keyword)', 'Identity command'],
            [9, 'ls (keyword)',     'Directory listing'],
            [10,'id (keyword)',     'User ID command'],
            [11,'/etc/ (path)',     'System config directory'],
            [12,'/var/ (path)',     'Variable data directory'],
            [13,'%XX (url encode)', 'URL percent encoding'],
            [14,'$IFS',             'Shell space variable'],
            [15,'sleep/wget/curl/nc','Network & timing commands'],
          ];
          foreach ($rules as $r): ?>
          <tr>
            <td style="color:var(--t3);font-size:.72rem"><?= $r[0] ?></td>
            <td style="color:var(--red)"><?= safe($r[1]) ?></td>
            <td style="color:var(--t2);font-family:inherit"><?= $r[2] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Ping Tool &mdash; WAF Active</div>
    <form method="POST" action="/advanced-3/">
      <div class="fg">
        <label class="fl">Host Input</label>
        <input class="fi" type="text" name="host"
          value="<?= safe($host) ?>"
          placeholder="Masukkan payload yang lolos dari semua 15 WAF rule...">
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-r">Execute</button>
        <?php if($executed||$blocked): ?><a href="/advanced-3/" class="btn btn-g">Reset</a><?php endif; ?>
      </div>
    </form>
  </div>

  <?php if ($blocked): ?>
  <div class="alert a-err">
    <strong>WAF BLOCKED</strong> &mdash; Pattern terdeteksi: <code class="ic"><?= safe($waf_reason) ?></code><br>
    <span style="font-size:.8rem">Input: <code class="ic"><?= safe($host) ?></code></span>
  </div>
  <?php endif; ?>

  <?php if ($executed): ?>
  <div class="term-hdr">
    <span class="term-dot" style="background:#22c55e"></span>
    <span class="term-dot" style="background:#22c55e"></span>
    <span class="term-dot" style="background:#22c55e"></span>
    <span class="term-title">WAF PASSED &mdash; Terminal Output</span>
  </div>
  <div class="terminal"><?= safe($output) ?></div>
  <?php if (str_contains($output, 'FLAG{') || str_contains($output, 'uid=')): ?>
  <div class="alert a-ok"><strong>WAF Bypass berhasil!</strong> Command dieksekusi melewati semua 15 rule.</div>
  <?php else: ?>
  <div class="alert a-warn">WAF dilewati, tapi belum ada command injection. Coba tambahkan payload injection ke input.</div>
  <?php endif; ?>
  <?php endif; ?>

  <div class="box">
    <div class="box-t">Advanced Evasion Techniques</div>
    <div class="qbox"><div class="ql">Teknik yang tidak tercakup WAF rules ini</div><span class="cm">-- 1. Newline (0x0a) sebagai separator — WAF hanya blokir % encoding, bukan literal newline</span>
<span class="cm">--    Gunakan Ctrl+V Ctrl+J di terminal, atau inject via curl --data-urlencode</span>

<span class="cm">-- 2. Brace expansion untuk reconstruct keyword tanpa spasi</span>
{c,a,t}       <span class="cm">-- menghasilkan: cat (huruf individual, bukan keyword 'cat')</span>
{i,d}         <span class="cm">-- menghasilkan: id</span>

<span class="cm">-- 3. Variable assignment dalam command</span>
<span class="val">a</span>=i;<span class="val">b</span>=d;<span class="kw">$a$b</span>       <span class="cm">-- WAF tidak lihat 'id', hanya 'a=i;b=d;$a$b'</span>
<span class="cm">--    TAPI: ; diblokir, jadi perlu cara lain assign variabel</span>

<span class="cm">-- 4. read dari /proc/self/environ untuk command execution info</span>
<span class="cm">-- 5. Base64 decode dan eval (jika ada perintah eval yang tersedia)</span>

<span class="cm">-- 6. Wildcard untuk path — /var/ diblokir tapi wildcard tidak</span>
/v?r/pr?vate/fl?g.txt    <span class="cm">-- ? cocok dengan satu karakter apapun</span>
/v*/pri*/flag*           <span class="cm">-- * cocok dengan string apapun</span></div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>
    <details class="hint">
      <summary>Hint 1 &mdash; Cari gap di WAF rules</summary>
      <div class="hint-body">Perhatikan rule #13: WAF hanya memblokir <code class="ic">%XX</code> URL encoding. Literal newline (byte <code class="ic">0x0a</code>) tidak terdeteksi. Bagaimana cara memasukkan newline literal ke dalam input?</div>
    </details>
    <details class="hint">
      <summary>Hint 2 &mdash; Bypass keyword filter dengan brace expansion</summary>
      <div class="hint-body">WAF memblokir kata <code class="ic">cat</code> dan <code class="ic">id</code> sebagai kata. Gunakan brace expansion:<br><code class="ic">{c,a,t}</code> menghasilkan string <code class="ic">cat</code> saat dieksekusi shell, tapi WAF hanya melihat <code class="ic">{c,a,t}</code>.<br>Sama untuk path: <code class="ic">/v?r/private/flag.txt</code> melewati blokir <code class="ic">/var/</code>.</div>
    </details>
    <details class="hint">
      <summary>Hint 3 &mdash; Gabungkan semua bypass</summary>
      <div class="hint-body">Gabungkan teknik: gunakan newline literal sebagai separator, brace expansion untuk keyword, dan wildcard untuk path.<br>Contoh pendekatan: <code class="ic">8.8.8.8[NEWLINE]{c,a,t}[SPACE]/v?r/pr?vate/fl?g.txt</code><br>Perlu juga mempertimbangkan bagaimana menghindari deteksi spasi — apakah ada karakter lain yang bisa digunakan?</div>
    </details>
    <details class="hint">
      <summary>Hint 4 &mdash; Keterbatasan WAF berbasis regex</summary>
      <div class="hint-body">WAF berbasis pattern/regex tidak bisa memahami konteks — ia hanya mencocokkan string. Selama kita tidak menggunakan string yang persis sama dengan yang ada di rule, kita bisa bypass. Defense yang benar: jangan gabungkan user input ke system command, gunakan allow-list ketat (hanya IP/hostname valid), dan jalankan command dengan privilege minimum.</div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
