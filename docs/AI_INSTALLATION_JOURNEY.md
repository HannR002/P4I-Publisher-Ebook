# Laporan Perjalanan Pemasangan Multi-Agent AI Environment
# Proyek: P4I Publisher Ebook — Antigravity IDE (Windows)

**Tanggal Mulai:** 24 September 2026
**Tanggal Selesai:** 25 September 2026
**Status Akhir:** Sebagian Berhasil — DeepSeek aktif, Claude/Astra terkendala budget pool AgentRouter

---

## Tujuan Awal

Mengonfigurasi Antigravity IDE (berbasis VS Code) sebagai multi-agent coding environment menggunakan AgentRouter sebagai AI gateway, dengan target:

- Claude Code CLI (model: claude-opus-4-8)
- Codex CLI (model: gpt-6-astra)
- Roo Code extension di Antigravity
- Profile: claude-opus-4-8, claude-opus-5, gpt-6-astra, deepseek-v4-flash
- Semua tanpa menyimpan API key di repository

---

## Tahap 1 — Audit Environment

**Hasil:**
- Node.js: v22.14.0 (memenuhi syarat >= 18)
- npm: 10.9.2
- Git: 2.47.1.windows.2
- npm global root: C:\npm-global
- claude: NOT FOUND (belum diinstall)
- codex: NOT FOUND (belum diinstall)

---

## Tahap 2 — Instalasi Claude Code CLI

```
npm install -g @anthropic-ai/claude-code@latest
```

**Hasil:**
- Berhasil diinstall: claude v2.1.281
- Masalah awal: command `claude` tidak ditemukan karena C:\npm-global belum ada di PATH
- Solusi: menambahkan `$env:PATH += ";C:\npm-global"` secara manual di sesi PowerShell

**Catatan:** PATH global npm tidak otomatis terdaftar di environment baru.

---

## Tahap 3 — Instalasi Codex CLI

```
npm install -g @openai/codex@latest
```

**Hasil:**
- Berhasil diinstall: codex-cli v0.156.1
- Konfigurasi awal `~/.codex/config.toml` dibuat dengan `wire_api = "chat"`

**Masalah ditemukan kemudian:**
Codex v0.156.1 menolak `wire_api = "chat"` dengan error:
```
wire_api = "chat" is no longer supported. Use wire_api = "responses"
```
**Solusi:** config.toml diupdate ke `wire_api = "responses"` dan backup dibuat.

---

## Tahap 4 — Pembuatan Script Keamanan

Dibuat dua script di `d:\P4I_Publisher_Ebook\tooling\`:

### setup-agentrouter.ps1
- Meminta API key via `Read-Host -AsSecureString` (input tersembunyi)
- Menawarkan dua pilihan: session saja atau Windows USER variable
- Mengatur: AGENTROUTER_API_KEY, ANTHROPIC_AUTH_TOKEN, ANTHROPIC_BASE_URL, ANTHROPIC_MODEL
- Tidak pernah menulis key ke file apapun

### ai-model.ps1
- Command: `.\ai-model.ps1 claude48 / claude5 / astra / deepseek`
- Mengubah ANTHROPIC_MODEL di session dan USER variable

---

## Tahap 5 — Masalah URL Endpoint (Kritis)

**Endpoint yang salah digunakan awalnya:**
```
https://co.agentrouter.org       (SALAH)
https://co.agentrouter.org/v1    (SALAH)
```

**Semua request menghasilkan 401 Invalid API Key!**

**Investigasi:**
- Browser subagent membaca dokumentasi resmi AgentRouter di agentrouter.org/portal/guide
- Dokumentasi menyebut prefix `ak-` sebagai format key — ini ternyata keliru/contoh saja
- Key aktual di konsol AgentRouter tetap `sk-` format

**URL yang benar (dari dokumentasi resmi):**
```
https://agentrouter.org          (Anthropic-compatible)
https://agentrouter.org/v1       (OpenAI-compatible)
```

**Penyebab kebingungan:**
`agentrouter.org` = situs utama + console + dashboard
`co.agentrouter.org` = dokumentasi saja (bukan API gateway aktif)

---

## Tahap 6 — Masalah Environment Variable Scope

**Masalah:** Script `setup-agentrouter.ps1` dijalankan dengan opsi 1 (session saja).
Background process yang dijalankan oleh agent memiliki scope berbeda dan tidak membaca variable dari terminal session pengguna.

**Solusi:** Pengguna menjalankan ulang script dengan opsi 2 (Windows USER environment variable).
Setelah ini, variable terdaftar di registry Windows dan tersedia untuk semua proses baru.

---

## Tahap 7 — Instalasi Ekstensi Antigravity

**Dicoba via CLI:**
```
code --install-extension rooveterinaryinc.roo-cline
code --install-extension saoudrizwan.claude-dev
```

**Hasil:**
- Roo Code v3.54.0: berhasil diinstall
- Claude Dev (Cline) v4.1.20: berhasil diinstall

**Catatan penting:** Roo Code mengumumkan End of Life — tidak akan menerima update lagi.
Tim Roo Code merekomendasikan beralih ke Cline. Namun ekstensi tetap berfungsi.

---

## Tahap 8 — Token Terekspos

Selama proses debugging, API key pertama (`sk-Gb0u...`) dibagikan di chat secara langsung oleh pengguna untuk mempercepat diagnosis.

**Tindakan yang diambil:**
- Token lama dihapus dari konsol AgentRouter
- Token baru dibuat dengan nama "new", quota $50, mode Chat

**Pelajaran:** Secret yang pernah masuk percakapan chat (termasuk dengan AI) tidak lagi aman karena tercatat di log percakapan.

---

## Tahap 9 — Konfigurasi Roo Code

**Setup wizard Roo Code:**
- Provider: OpenAI Compatible
- Base URL: https://agentrouter.org/v1
- Model: claude-opus-4-8
- API Key: dimasukkan manual via SecretStorage Roo Code

**Uji pertama:** 401 UNAUTHENTICATED
**Uji kedua (token baru):** 402 Budget Pool Exhausted

**Analisis error 402:**
- Roo Code mengirim request ke `claude-opus-5` (bukan claude-opus-4-8) di attempt pertama
- Error: "Budget pool quota has been exhausted"
- Wallet menunjukkan $200 balance, namun budget pool untuk model premium (Claude, GPT-6-Astra) terpisah

---

## Tahap 10 — Verifikasi Koneksi Berhasil

**Test yang berhasil:** OK CLAUDE
- Model: claude-opus-4-8 via AgentRouter
- Biaya: $0.07
- Wallet: $200.00 → $199.93

Ini membuktikan seluruh stack berfungsi: Roo Code → AgentRouter → Claude Opus 4.8

**Namun setelah itu, claude-opus-4-8 kembali memberikan 402.**
Hipotesis: Budget pool Claude/GPT di akun ini sangat terbatas (mungkin promo/trial pool).

---

## Tahap 11 — Model Aktif: DeepSeek V4 Flash

**Penemuan:** `deepseek-v4-flash` berfungsi normal tanpa error budget pool.

**Analisis:**
- AgentRouter menggunakan sistem "budget pool" per model group
- DeepSeek ada di pool yang berbeda (lebih murah/lebih bebas akses)
- Claude Opus dan GPT-6-Astra kemungkinan di pool premium dengan quota sangat terbatas
- Saldo $199.93 di wallet bisa digunakan jika pool dihubungkan — perlu konfigurasi di sisi AgentRouter

---

## Status Akhir

| Komponen | Status |
|----------|--------|
| Roo Code di Antigravity | AKTIF |
| DeepSeek V4 Flash | BERFUNGSI |
| Claude Opus 4.8 | TERBATAS (budget pool) |
| GPT-6-Astra | TIDAK BISA (budget pool) |
| Claude Code CLI | TERINSTALL (belum ditest langsung) |
| Codex CLI | TERINSTALL (config sudah benar) |
| Security | AMAN (tidak ada key di repo) |

---

## Konfigurasi Aktif

### Roo Code Settings (Profile Aktif)
```
API Provider : OpenAI Compatible
Base URL     : https://agentrouter.org/v1
Model        : deepseek-v4-flash
```

### Codex CLI (~/.codex/config.toml)
```toml
model = "gpt-6-astra"
model_provider = "agentrouter"

[model_providers.agentrouter]
name = "AgentRouter"
base_url = "https://agentrouter.org/v1"
env_key = "AGENTROUTER_API_KEY"
wire_api = "responses"
```

### Windows USER Environment Variables
```
AGENTROUTER_API_KEY = (tersimpan aman di registry Windows)
ANTHROPIC_AUTH_TOKEN = (tersimpan aman di registry Windows)
ANTHROPIC_BASE_URL = https://agentrouter.org
ANTHROPIC_MODEL = claude-opus-4-8
```

---

## Tindak Lanjut yang Diperlukan

### Untuk Mengaktifkan Claude dan GPT-6-Astra:
1. Login ke https://agentrouter.org/console
2. Cek halaman Wallet — pastikan saldo dapat dialokasikan ke budget pool Claude/GPT
3. Atau hubungi support AgentRouter di Discord: discord.gg/HgekCyHJqB
4. Tanyakan: "How to allocate wallet balance to Claude/GPT-6-Astra budget pool?"

### Alternatif Jangka Panjang:
- Gunakan DeepSeek V4 Flash untuk pekerjaan sehari-hari (berfungsi penuh)
- Pertimbangkan langgganan langsung ke Anthropic API atau OpenAI API untuk Claude/GPT tanpa routing
- Roo Code EOL — pertimbangkan migrasi ke Cline (saoudrizwan.claude-dev) yang sudah terinstall

---

## File yang Dibuat/Dimodifikasi

| File | Keterangan |
|------|------------|
| tooling/setup-agentrouter.ps1 | Script setup API key (aman) |
| tooling/ai-model.ps1 | Model switcher |
| .gitignore | Ditambahkan aturan proteksi secret |
| AI_AGENTS_SETUP.md | Dokumentasi penggunaan |
| ~/.codex/config.toml | Konfigurasi Codex CLI |
| ~/.codex/config.toml.bak | Backup config Codex |

---

## Security Audit

- API key di repository: TIDAK ADA
- API key di .env: TIDAK ADA
- API key di Markdown/JSON/TOML: TIDAK ADA
- API key terekspos di chat: PERNAH TERJADI (token lama sudah dihapus)
- Token aktif saat ini: Disimpan di Windows registry + Roo Code SecretStorage

---

## Tahap 12 — Verifikasi Versi Model DeepSeek (26 September 2026)

**Pertanyaan:** apakah alias `deepseek-v4-flash` di AgentRouter menunjuk ke
V4 Flash lama atau V4.1 Flash terbaru?

**Jawaban:** **BELUM DIKETAHUI.** Verifikasi gagal di tahap autentikasi.
Tidak ada kesimpulan yang dibuat-buat.

### Metodologi

Dibuat dua script baru di `tooling/`:

- `check-deepseek-version.ps1` — menangkap route catalogue, raw response body,
  seluruh header, resolved identifier (`model`, `id`, `system_fingerprint`,
  `owned_by`), dan behavioural probe
- `diagnose-agentrouter-auth.ps1` — mendiagnosis 401 dengan matriks User-Agent
  dan path, serta melaporkan identitas key secara aman

Kedua script membaca key **hanya** dari `$env:AGENTROUTER_API_KEY`,
tidak pernah dari file atau argumen CLI.

### Temuan 1 — Blocker Autentikasi (Root Cause Ditemukan)

Ketiga request (`/v1/models`, `/v1/chat/completions`, probe) mengembalikan 401:

```json
{"error":{"message":"unauthorized client detected, contact support ..."},
 "message":"UNAUTHENTICATED","success":false,
 "type":"unauthorized_client_error"}
```

Perhatikan: **`unauthorized client`**, bukan `invalid api key`.

Diagnostic melaporkan identitas key:

```
Masked prefix      : sk-Gb0...
Length             : 51 characters
SHA-256 fingerprint: 37dd30fee23933d4
Present in Process : True
Present in User    : True
```

Prefix `sk-Gb0...` **cocok** dengan token pertama `sk-Gb0u...` yang menurut
Tahap 8 sudah **dihapus dari konsol**.

> **Kesimpulan:** environment variable `AGENTROUTER_API_KEY` masih berisi token
> yang sudah DICABUT. Pencabutan di sisi server sudah dilakukan, tetapi salinan
> lokal tidak pernah dibersihkan. Roo Code tetap bekerja karena memakai
> SecretStorage sendiri, sehingga kegagalan ini tersembunyi.

### Temuan 2 — Hipotesis User-Agent DITOLAK

Diuji 5 User-Agent berbeda terhadap `/v1/models`:

| User-Agent | Status |
|------------|--------|
| (tidak ada) | 401 |
| `Roo-Code/3.54.0 (Antigravity IDE)` | 401 |
| `OpenAI/Python 1.55.0` | 401 |
| `claude-cli/2.1.281 (external, cli)` | 401 (body berbeda) |
| `curl/8.4.0` | 401 |

User-Agent **bukan** penyebabnya. Namun UA Claude CLI menghasilkan body berbeda
(`type: new_api_error`), yang mengungkap stack gateway.

### Temuan 3 — Arsitektur Gateway Terungkap

Header response membocorkan arsitektur yang sebelumnya tidak terdokumentasi:

```
X-Oneapi-Request-Id: 20260926012857722516269q4bmqfRt4kVj5
Set-Cookie: acw_tc=0a0ccbcb...;path=/;HttpOnly;Max-Age=1800
```

```
Client → Aliyun WAF → new-api/one-api → upstream provider
```

| Fakta | Bukti |
|-------|-------|
| Gateway berbasis **new-api / one-api** | Header `X-Oneapi-Request-Id` |
| Ada **Aliyun WAF** di depan | Cookie `acw_tc` |
| Region **China (UTC+8)** | Timestamp `20260926012857` = 01:28:57 UTC+8 |
| Base URL `/v1` benar | `/models` mengembalikan HTML, `/v1/models` mengembalikan JSON |

Konsekuensi: karena ini new-api, mapping alias ke model upstream **sepenuhnya
ditentukan server**. Nama alias tidak menjamin versi.

### Temuan 4 — Dua Script Rusak Ditemukan

| File | Masalah |
|------|---------|
| `tooling/ai-model.ps1` | Branch `deepseek` hanya mencetak pesan, **tidak mengubah variable apapun** |
| `tooling/setup-agentrouter.ps1` | Selalu menulis `ANTHROPIC_MODEL=claude-opus-4-8`; tidak pernah mendukung DeepSeek |

Keduanya sudah diperbaiki. `ai-model.ps1` kini benar-benar menetapkan
`ANTHROPIC_MODEL` dan `AGENTROUTER_MODEL`, terverifikasi lewat eksekusi langsung.

### Temuan 5 — Klaim Keamanan Dikoreksi

Dokumen sebelumnya menyebut key "tersimpan aman di registry Windows".
`SetEnvironmentVariable(..., "User")` menulis **PLAINTEXT** ke
`HKCU\Environment`, tanpa enkripsi. Klaim tersebut sudah dikoreksi di
`setup-agentrouter.ps1` dan `AI_AGENTS_SETUP.md`.

### Deliverable

| File | Isi |
|------|-----|
| `tooling/check-deepseek-version.ps1` | Capture bukti versi model |
| `tooling/diagnose-agentrouter-auth.ps1` | Diagnostik 401 + identitas key |
| `tooling/ai-model.ps1` | Diperbaiki (sekarang benar-benar berfungsi) |
| `tooling/setup-agentrouter.ps1` | Diperbaiki + disclosure keamanan |
| `docs/AGENTROUTER_MODEL_VERIFICATION.md` | Laporan lengkap + instruksi Step E |
| `docs/evidence/agentrouter-verification-20260926-002857/` | Raw evidence (4 file) |

### Tindak Lanjut

1. Jalankan `.\tooling\setup-agentrouter.ps1` dengan token yang **valid** dari konsol
2. Konfirmasi `.\tooling\diagnose-agentrouter-auth.ps1` menampilkan **200**
3. Jalankan `.\tooling\check-deepseek-version.ps1` untuk menangkap metadata versi
4. Lengkapi Step E: baca Usage Log dan daftar model di konsol
5. Hanya setelah bukti ada, perbarui tabel model dengan versi yang terkonfirmasi

**Status pertanyaan awal: MASIH TERBUKA.** Tidak ada asumsi yang dibuat.
