# Lab Command Injection — IDN-CyberRange

<div align="center">

Lingkungan praktik OS Command Injection berbasis Docker dengan enam modul bertingkat — dari basic injection hingga WAF evasion tingkat lanjut.

[![Port](https://img.shields.io/badge/Port-8084-2496ED)](#cara-menjalankan)
[![Modules](https://img.shields.io/badge/Modules-6-22c55e)](#modul)
[![Flags](https://img.shields.io/badge/Flags-6-e63946)](#flags--challenges)
[![Stack](https://img.shields.io/badge/Stack-PHP%208.1%20%2B%20MySQL-777BB4)](#arsitektur)

</div>

---

## Daftar Isi

- [Tentang Lab](#tentang-lab)
- [Modul](#modul)
- [Arsitektur](#arsitektur)
- [Cara Menjalankan](#cara-menjalankan)
- [Struktur File](#struktur-file)
- [Database](#database)
- [Flags & Challenges](#flags--challenges)
- [Command Separators](#command-separators)
- [Commands](#commands)
- [Disclaimer](#disclaimer)

---

## Tentang Lab

OS Command Injection terjadi ketika aplikasi meneruskan input pengguna ke sistem operasi shell tanpa sanitasi yang memadai, memungkinkan attacker mengeksekusi perintah arbitrer di server. Lab ini menyimulasikan skenario aplikasi nyata seperti network diagnostic tools, DNS lookup, dan file processing utilities yang rentan — enam modul bertingkat dari blind injection, filter bypass, Out-of-Band exfiltration, hingga WAF evasion.

---

## Modul

### Basic Series

| # | Nama | Path | Tingkat | Deskripsi |
|---|------|------|---------|-----------|
| 1 | Basic CMDi | `/basic-1/` | Basic | Tool ping menggunakan input host langsung di `shell_exec()` tanpa sanitasi |
| 2 | Blind CMDi | `/basic-2/` | Basic | Output disembunyikan — gunakan time-based dan output redirection |
| 3 | CMDi via Multiple Fields | `/basic-3/` | Basic | DNS lookup tool dengan dua field — hanya satu yang divalidasi |

### Advanced Series

| # | Nama | Path | Tingkat | Deskripsi |
|---|------|------|---------|-----------|
| 4 | CMDi + Filter Bypass | `/advanced-1/` | Advanced | Bypass blacklist filter 3 level dengan encoding, `$IFS`, brace expansion |
| 5 | Blind CMDi + OOB | `/advanced-2/` | Advanced | Out-of-Band exfiltration via HTTP callback dan DNS — tanpa output langsung |
| 6 | CMDi + WAF Evasion | `/advanced-3/` | Advanced | Bypass WAF 15-pattern menggunakan teknik obfuscation tingkat lanjut |

---

## Arsitektur

```
Container: lab-cmdi  (port 8084)
├── Supervisor
│   ├── Apache 2 + PHP 8.1  -->  port 8084
│   └── MySQL 8.0           -->  internal only
└── /var/www/html/
    ├── /basic-1/   /basic-2/   /basic-3/
    └── /advanced-1/ /advanced-2/ /advanced-3/
```

Port mapping seluruh lab CyberRange:

| Lab | Container | Port |
|-----|-----------|------|
| Command Injection | `lab-cmdi` | `8084` |

---

## Cara Menjalankan

### Prasyarat

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER && newgrp docker
```

### Opsi A — Build dari Source Code

```bash
git clone https://github.com/15K4R10T/IDN-CyberRange.git
cd IDN-CyberRange/lab-cmdi
chmod +x run.sh
./run.sh
```

### Opsi B — Load dari Docker Image
tar file (https://drive.google.com/file/d/10Pvo3co_Ojhu7bzIDm5cPigR3kuE3DRv/view?usp=sharing)
```bash
docker load < lab-cmdi-image.tar.gz
docker run -d --name lab-cmdi -p 8084:80 --restart unless-stopped lab-cmdi
```

### Akses Lab

```
http://localhost:8084
http://<IP-VM>:8084
```

---

## Struktur File

```
lab-cmdi/
├── Dockerfile                  Termasuk instalasi tools: ping, nmap, curl, nslookup
├── run.sh                      Build & deploy otomatis (port 8084)
├── entrypoint.sh
├── supervisord.conf
├── apache.conf
├── init.sql                    Schema: dns_lookup_log, ping_log, whois_log
└── web/
    ├── index.php               Dashboard
    ├── basic-1/index.php       Modul 1: Basic CMDi — ping tool
    ├── basic-2/index.php       Modul 2: Blind CMDi — output redirection
    ├── basic-3/index.php       Modul 3: Multi-field CMDi — DNS lookup
    ├── advanced-1/index.php    Modul 4: Filter Bypass 3 level
    ├── advanced-2/index.php    Modul 5: Blind OOB — HTTP callback collector
    ├── advanced-3/index.php    Modul 6: WAF Evasion 15 rules
    └── includes/
        ├── db.php
        ├── shared_css.php
        ├── nav.php
        └── footer.php
```

---

## Database

```
Host     : 127.0.0.1
Database : labcmdi
User     : labuser
Password : labpass123
```

| Tabel | Keterangan |
|-------|-----------|
| `dns_lookup_log` | Log query DNS — digunakan modul Basic 3 |
| `ping_log` | Log hasil ping — digunakan modul Basic 1 |
| `whois_log` | Log whois query |

---

## Flags & Challenges

| Flag | Modul | Cara Mendapatkan |
|------|-------|-----------------|
| `FLAG{cmdi_basic_rce}` | Basic 1 | `cat /var/private/flag.txt` via semicolon injection |
| `FLAG{cmdi_blind_redirect}` | Basic 2 | Redirect flag ke `/tmp/cmdi_output.txt` lalu baca |
| `FLAG{cmdi_multifield_bypass}` | Basic 3 | Inject pada field domain di DNS lookup tool |
| `FLAG{cmdi_filter_bypass}` | Advanced 1 | Bypass semua 3 level blacklist filter |
| `FLAG{cmdi_oob_exfil}` | Advanced 2 | Eksfiltrasi flag via HTTP callback ke OOB collector |
| `FLAG{cmdi_waf_evasion}` | Advanced 3 | Bypass semua 15 WAF rules dan baca flag |

---

## Command Separators

| Separator | Nama | Perilaku |
|-----------|------|---------|
| `;` | Semicolon | Jalankan command berikutnya apapun exit code sebelumnya |
| `&&` | AND | Jalankan hanya jika command sebelumnya berhasil |
| `\|\|` | OR | Jalankan hanya jika command sebelumnya gagal |
| `\|` | Pipe | Kirim output sebagai input command berikutnya |
| `` `cmd` `` | Backtick | Command substitution — output disubstitusi ke dalam string |
| `$(cmd)` | Subshell | Command substitution modern — bisa nested |

---

## Commands

```bash
docker logs -f lab-cmdi
docker stop lab-cmdi
docker start lab-cmdi
docker exec -it lab-cmdi bash
docker rm -f lab-cmdi && ./run.sh
```

---

## Disclaimer

> Lab ini dibuat **hanya untuk keperluan edukasi dan pelatihan keamanan siber** di lingkungan yang terisolasi.
> Jangan gunakan teknik yang dipelajari pada sistem, jaringan, atau aplikasi tanpa izin tertulis dari pemiliknya.
> ID-Networkers tidak bertanggung jawab atas segala bentuk penyalahgunaan materi dalam repositori ini.

---

<div align="center">
  <sub>Dibuat oleh <strong>ID-Networkers</strong> — Indonesian IT Expert Factory</sub>
</div>
