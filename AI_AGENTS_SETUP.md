# AI Agents Setup — P4I Publisher Ebook

> Dokumen ini TIDAK mengandung API key, token, password, atau credential apapun.

---

## ⚠️ Status Terverifikasi (diperbarui 26 September 2026)

| Item | Status | Catatan |
|------|--------|---------|
| Roo Code (Antigravity) | AKTIF | `OK CLAUDE` confirmed |
| AgentRouter — dari Roo Code | BERFUNGSI | Memakai key di Roo Code SecretStorage |
| AgentRouter — dari CLI/script | **GAGAL 401** | Environment variable berisi token yang SUDAH DICABUT |
| Versi DeepSeek yang dilayani | **BELUM DIKETAHUI** | Lihat [`docs/AGENTROUTER_MODEL_VERIFICATION.md`](docs/AGENTROUTER_MODEL_VERIFICATION.md) |
| Claude Opus 4.8 | TERBATAS | Budget pool |
| GPT-6-Astra | TIDAK BISA | Budget pool |
| Claude Code CLI | v2.1.281 | Terinstall, belum lolos auth |
| Codex CLI | v0.156.1 | Terinstall, belum lolos auth |

> **PENTING:** dokumentasi ini sebelumnya mengklaim `deepseek-v4-flash` "berfungsi normal".
> Klaim itu hanya membuktikan konektivitas, dan **tidak** membuktikan versi model.
> Verifikasi versi masih tertunda karena blocker autentikasi.

---

## 0. Arsitektur AgentRouter (Fakta Terverifikasi)

Hasil probe langsung mengungkap arsitektur gateway yang sebelumnya tidak terdokumentasi:

```
Roo Code / CLI client
        │
        ▼
Aliyun WAF          ← bukti: cookie acw_tc + Set-Cookie pada response 401
        │
        ▼
new-api / one-api   ← bukti: header X-Oneapi-Request-Id + request-id format
        │
        ▼
Upstream provider (DeepSeek / Anthropic / OpenAI)
```

| Fakta | Bukti |
|-------|-------|
| Gateway berbasis **new-api / one-api** | Header `X-Oneapi-Request-Id`, error `type: new_api_error` |
| Ada **Aliyun WAF** di depan API | Cookie `acw_tc=...` dengan `HttpOnly; Max-Age=1800` |
| Deployment region **China (UTC+8)** | Timestamp request-id `20260926012857` = 2026-09-26 01:28:57 UTC+8 |
| Base URL OpenAI-compatible benar | `/v1/models` merespons JSON; `/models` mengembalikan HTML web app |
| Ada middleware **"unauthorized client"** | Body 401 menyebut `unauthorized_client_error`, bukan `invalid api key` |

Implikasi: karena ini **new-api**, alias model sepenuhnya ditentukan server.
Nama alias `deepseek-v4-flash` tidak menjamin versi upstream tertentu.

---

## 1. Roo Code — Cara Utama (Sudah Berfungsi)

Buka ikon Roo Code di sidebar kiri Antigravity, ketik instruksi di kolom chat.

### Profile AgentRouter-Claude (VERIFIED)

| Field | Nilai |
|-------|-------|
| API Provider | OpenAI Compatible |
| Base URL | https://agentrouter.org/v1 |
| Model | claude-opus-4-8 |
| API Key | Input via Roo Code Settings (SecretStorage) |

### Profile AgentRouter-DeepSeek

| Field | Nilai |
|-------|-------|
| API Provider | OpenAI Compatible |
| Base URL | https://agentrouter.org/v1 |
| Model | deepseek-v4-flash |
| API Key | Input via Roo Code Settings (SecretStorage) |

### Profile AgentRouter-Astra

| Field | Nilai |
|-------|-------|
| API Provider | OpenAI Compatible |
| Base URL | https://agentrouter.org/v1 |
| Model | gpt-6-astra |
| API Key | Input via Roo Code Settings (SecretStorage) |

> Roo Code menyimpan key di SecretStorage-nya sendiri, terpisah dari environment variable.
> Inilah sebabnya Roo Code tetap bekerja walau environment variable sudah kedaluwarsa.

---

## 2. AgentRouter Endpoints

| Protokol | Base URL | Terverifikasi |
|----------|----------|---------------|
| OpenAI Compatible | https://agentrouter.org/v1 | ✅ merespons JSON |
| Anthropic Compatible | https://agentrouter.org | ✅ dipakai Claude Code CLI |

Catatan: `https://co.agentrouter.org` adalah situs dokumentasi, **bukan** gateway API.

---

## 3. Model yang Tersedia (Daftar Lengkap — Terverifikasi dari Server)

Sumber: `https://agentrouter.org/api/pricing` (**publik, tidak butuh API key**).
Bukti mentah: [`docs/evidence/agentrouter-public-models.json`](docs/evidence/agentrouter-public-models.json)

| Model ID | Input ratio | Output ratio | Protokol | Versi Upstream |
|----------|-------------|--------------|----------|----------------|
| claude-opus-4-8 | 4 | 5 | anthropic, openai | `owner_by` kosong |
| claude-opus-5 | 3 | 5 | anthropic, openai | `owner_by` kosong |
| **deepseek-v4-flash** | **2** | **3** | openai, anthropic | `owner_by` kosong |
| gpt-6-astra | 2 | 5 | openai | `owner_by` kosong |

> ### ✅ Hanya ADA SATU route DeepSeek: `deepseek-v4-flash`
> Tidak ada `deepseek-v4.1-flash`, tidak ada `deepseek-chat`, tidak ada `deepseek-reasoner`,
> tidak ada varian berversi apa pun. Daftar di atas **lengkap** — gateway hanya mengekspos 4 route.
>
> Karena itu, **penamaan route TIDAK BISA** menjawab pertanyaan V4 vs V4.1: tidak ada route kedua
> untuk dibandingkan. Opsi `deepseek41` di [`tooling/ai-model.ps1`](tooling/ai-model.ps1) **sengaja
> ditolak** karena route targetnya tidak ada.

**Jangan mengasumsikan** `deepseek-v4-flash` menunjuk ke V4.1 Flash. AgentRouter pemilik mapping
alias tersebut, dan field `owner_by` **kosong untuk semua model** sehingga versi upstream tidak
diungkap. Untuk mencoba mengungkapnya (butuh token aktif):

```powershell
.\tooling\check-deepseek-version.ps1
```

### Benchmark

AgentRouter **tidak menerbitkan** Accuracy, pass@1, avg@3, Elo, atau spesifikasi harness untuk
route mana pun. Definisi tiap istilah dan batas apa yang bisa diukur ada di
[`docs/BENCHMARK_AND_SCORING.md`](docs/BENCHMARK_AND_SCORING.md). Untuk mengukur sendiri:

```powershell
.\tooling\check-agentrouter-capabilities.ps1
```

---

## 4. Setup API Key (Jalankan Sekali)

```powershell
.\tooling\setup-agentrouter.ps1
```

Pilih opsi 1 (session saja) atau opsi 2 (Windows USER variable).

### 🔐 Peringatan Keamanan yang Dikoreksi

Versi lama dokumen ini menyatakan key "tersimpan aman di registry Windows".
**Klaim itu terlalu optimistis.**

`SetEnvironmentVariable(name, value, "User")` menulis nilai sebagai **PLAINTEXT**
ke `HKEY_CURRENT_USER\Environment`. Tidak ada enkripsi. Proses lain milik user yang
sama dapat membacanya.

| Opsi | Keamanan | Kegunaan |
|------|----------|----------|
| 1 — Session only | Terbaik untuk sesi pendek | Tidak menulis ke disk |
| 2 — Windows User | **Plaintext, tidak terenkripsi** | Praktis, lintas reboot |

Untuk secret produksi, gunakan **Windows Credential Manager** atau Roo Code SecretStorage.

Script mencetak **masked prefix** dan **SHA-256 fingerprint** setelah setup.
Bandingkan prefix tersebut dengan daftar token di
https://agentrouter.org/console/token untuk memastikan key yang benar.

---

## 5. Verifikasi Wajib Setelah Setup

```powershell
.\tooling\diagnose-agentrouter-auth.ps1
```

Script ini menguji 6 kombinasi URL dan User-Agent, lalu melaporkan verdict.
Output yang benar harus menampilkan **status 200** untuk probe `/v1/models`.

Jika masih 401, key belum valid — lihat bagian Troubleshooting.

---

## 6. Claude Code CLI

- Endpoint: https://agentrouter.org
- Model default: deepseek-v4-flash
- Command: claude

Environment variables yang diperlukan:
- ANTHROPIC_BASE_URL = https://agentrouter.org
- ANTHROPIC_AUTH_TOKEN = (API Key AgentRouter)
- ANTHROPIC_MODEL = deepseek-v4-flash

---

## 7. Codex CLI

Config: `~\.codex\config.toml` (dikonfigurasi statis)

- Endpoint: https://agentrouter.org/v1
- Model default: gpt-6-astra
- Command: codex

Catatan: Codex **tidak** membaca environment variable untuk pemilihan model.
Ubah field `model` di `config.toml` secara langsung.

---

## 8. Model Switcher

```powershell
.\tooling\ai-model.ps1 claude48    # claude-opus-4-8
.\tooling\ai-model.ps1 claude5     # claude-opus-5
.\tooling\ai-model.ps1 deepseek    # deepseek-v4-flash
.\tooling\ai-model.ps1 astra       # gpt-6-astra
.\tooling\ai-model.ps1 deepseek -Persist   # tulis permanen ke Windows User
```

*(Catatan: Opsi `deepseek41` pernah ada di daftar ini, tetapi telah dihapus karena `/api/pricing` membuktikan bahwa route `deepseek-v4.1-flash` **tidak ada** di AgentRouter).*

### Perbaikan yang Dilakukan

Versi lama script ini **tidak berfungsi** untuk `deepseek` dan `astra`:

- Branch `deepseek` hanya mencetak pesan, **tidak mengubah variable apapun**
- Branch `astra` hanya mencetak instruksi

Versi baru benar-benar menetapkan `ANTHROPIC_MODEL` dan `AGENTROUTER_MODEL`.
Terverifikasi: setelah `ai-model.ps1 deepseek`, nilai `$env:ANTHROPIC_MODEL`
menjadi `deepseek-v4-flash`.

---

## 9. Script Tooling

| Script | Fungsi |
|--------|--------|
| [`tooling/setup-agentrouter.ps1`](tooling/setup-agentrouter.ps1) | Menyimpan API key + disclosing risiko storage |
| [`tooling/diagnose-agentrouter-auth.ps1`](tooling/diagnose-agentrouter-auth.ps1) | Mendiagnosis 401: uji User-Agent & path, cek identitas key |
| [`tooling/check-deepseek-version.ps1`](tooling/check-deepseek-version.ps1) | Menangkap bukti versi model dari response metadata |
| [`tooling/ai-model.ps1`](tooling/ai-model.ps1) | Model switcher (sudah diperbaiki) |

---

## 10. Security

- API key TIDAK tersimpan di repository
- API key TIDAK ada di .env, JSON, TOML, atau Markdown
- API key hanya diterima via environment variable, tidak via file atau argumen CLI
- Script hanya mencetak masked prefix dan fingerprint satu arah (SHA-256)
- JANGAN kirim .env, SSH key, database password, GitHub token ke AI agent

### Pelajaran dari Insiden Token Bocor

Token pertama (`sk-Gb0u...`) pernah dibagikan di chat, lalu dicabut di konsol.
**Namun pencabutan itu tidak lengkap**: salinan token tersebut masih tertinggal di
environment variable Windows, dan terus dipakai oleh setiap CLI/script.

Roo Code tidak terpengaruh karena memakai SecretStorage sendiri, sehingga
kegagalan ini **tersembunyi** — Roo Code bekerja, script tidak.

**Aturan:** setiap kali token dicabut, jalankan ulang
`.\tooling\setup-agentrouter.ps1` dengan token pengganti, lalu
`.\tooling\diagnose-agentrouter-auth.ps1` untuk konfirmasi.

---

## 11. Troubleshooting

### Error 401 UNAUTHENTICATED

Gejala: body berisi `"unauthorized client detected"` dan `type: unauthorized_client_error`.

**Bukan** masalah User-Agent. Sudah diuji 5 User-Agent berbeda, semuanya 401.

Penyebab paling mungkin: environment variable berisi token yang sudah dicabut.

Langkah:
1. Jalankan `.\tooling\diagnose-agentrouter-auth.ps1`
2. Catat `Masked prefix` yang dilaporkan
3. Buka https://agentrouter.org/console/token dan bandingkan dengan token bernama "new"
4. Jika tidak cocok, jalankan `.\tooling\setup-agentrouter.ps1` dengan token yang benar
5. Ulangi langkah 1 sampai status 200

### Error 402 Budget Pool Exhausted

- Cek saldo: https://agentrouter.org/console/wallet
- Budget pool untuk model premium (Claude/GPT-6) terpisah dari saldo wallet
- Hubungi support AgentRouter: discord.gg/HgekCyHJqB
- Coba model lain: deepseek-v4-flash

### Versi DeepSeek Tidak Diketahui

Lihat [`docs/AGENTROUTER_MODEL_VERIFICATION.md`](docs/AGENTROUTER_MODEL_VERIFICATION.md)
bagian Step E untuk prosedur pembacaan Usage Log di konsol.

### Command claude atau codex tidak ditemukan

Tambahkan ke PATH di PowerShell:
```powershell
$env:PATH += ";C:\npm-global"
```

### Roo Code tidak muncul di sidebar

Tekan Ctrl+Shift+P lalu ketik: `Roo Code: Focus on Roo Code View`

---

## 12. Referensi

- Benchmark & definisi skor: [`docs/BENCHMARK_AND_SCORING.md`](docs/BENCHMARK_AND_SCORING.md)
- Laporan verifikasi model: [`docs/AGENTROUTER_MODEL_VERIFICATION.md`](docs/AGENTROUTER_MODEL_VERIFICATION.md)
- Perjalanan instalasi: [`docs/AI_INSTALLATION_JOURNEY.md`](docs/AI_INSTALLATION_JOURNEY.md)
- AgentRouter Roo Code Docs: https://agentrouter.org/docs/roocode.html
- AgentRouter Console: https://agentrouter.org/console/token
- Claude Code Docs: https://docs.anthropic.com/claude-code
