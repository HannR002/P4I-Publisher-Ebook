# AI Installation Journey — Addendum Tahap 13

> Lanjutan dari [`docs/AI_INSTALLATION_JOURNEY.md`](AI_INSTALLATION_JOURNEY.md) (Tahap 1–12).
> Dipisahkan sebagai addendum karena `apply_diff` gagal berulang pada file induk
> (indikasi masalah BOM/encoding), dan menulis ulang 403 baris secara utuh berisiko
> merusak riwayat yang sudah ada.

**Tanggal:** 26 September 2026
**Konteks masuk:** Blocked 401 pada Tahap 12, pertanyaan versi DeepSeek masih terbuka.

---

## Temuan 6 — Endpoint publik `/api/pricing` (BREAKTHROUGH)

Blocker 401 tetap ada, tetapi ditemukan jalan memutar yang menjawab sebagian besar
pertanyaan **tanpa API key sama sekali**.

`https://agentrouter.org/api/pricing` **terbuka tanpa autentikasi** dan mengembalikan
roster model lengkap dari server. Ini melewati blocker 401 sepenuhnya.

| Model ID | Input ratio | Output ratio | Protokol |
|----------|-------------|--------------|----------|
| claude-opus-4-8 | 4 | 5 | anthropic, openai |
| claude-opus-5 | 3 | 5 | anthropic, openai |
| **deepseek-v4-flash** | **2** | **3** | openai, anthropic |
| gpt-6-astra | 2 | 5 | openai |

Konsekuensi langsung:

- **Hanya SATU route DeepSeek.** Tidak ada `deepseek-v4.1-flash`, tidak ada varian berversi.
- Penamaan route **tidak bisa** menjawab V4 vs V4.1 — tidak ada route kedua untuk dibandingkan.
- Field `owner_by` **kosong untuk semua model**, jadi build upstream tetap tidak diungkap.
- Opsi `deepseek41` di [`tooling/ai-model.ps1`](../tooling/ai-model.ps1) menargetkan route
  yang **tidak ada** → diperbaiki menjadi penolakan eksplisit (exit code 2).

Bukti mentah: [`docs/evidence/agentrouter-public-models.json`](evidence/agentrouter-public-models.json)

---

## Temuan 7 — Gotcha PowerShell 5.1

`ConvertFrom-Json` gagal dengan:

```
Cannot process argument because the value of argument "name" is not valid.
```

saat objek JSON punya **key string kosong**. AgentRouter mengembalikan:

```json
"usable_group": { "": "用户分组", "default": "默认分组" }
```

Perbaikan: `$body -replace '\{"":', '{"_blank":'` sebelum parsing.

Dua kegagalan pertama yang berpesan **"Response was not JSON"** sebenarnya respons
**JSON valid** (985 byte, HTTP 200). Pesan errornya menyesatkan. Tercatat agar tidak
terulang: jangan percaya pesan "not JSON" dari PowerShell tanpa memverifikasi byte mentah.

---

## Temuan 8 — Harness benchmark dibuat dan dijalankan

Pertanyaan pengguna: metrik seperti **Accuracy, pass@1, avg@3, Elo, harness,
reasoning effort, with tools, strict/partial, standard error** untuk model yang dipakai.

Dua deliverable:

| File | Isi |
|------|-----|
| [`docs/BENCHMARK_AND_SCORING.md`](BENCHMARK_AND_SCORING.md) | Definisi + rumus tiap istilah, batas epistemik |
| [`tooling/check-agentrouter-capabilities.ps1`](../tooling/check-agentrouter-capabilities.ps1) | Harness yang benar-benar **mengukur**: pass@1, avg@3, strict/partial, latency median+p95, SE, tool_calls |

**Hasil eksekusi live:** 15/15 panggilan → **401**, sehingga:

```
 Valid calls        : 0/15
 pass@1 (strict)    : 0/15 = 0
 avg@3 (partial)   : 0
 Standard error     : 0
```

> Ini **bukan** skor model. Ini kredensial yang ditolak sebelum penilaian dimulai.
> Harness melaporkan keadaan itu secara jujur alih-alih mengarang angka.

---

## Keputusan yang diambil

1. **Menolak mengutip angka benchmark DeepSeek resmi** untuk alias `deepseek-v4-flash`.
   Itu kesalahan atribusi: yang diukur label gateway, bukan build upstream terverifikasi.
2. **Menandai metrik tak terukur** (Accuracy, Elo, spesifikasi harness) sebagai
   *structurally unavailable*, bukan "belum dicek".
3. **Menyediakan pengukuran sendiri** sebagai satu-satunya jalan sah menuju angka nyata.

---

## Deliverable Tahap 13

| File | Status |
|------|--------|
| [`tooling/check-agentrouter-public-models.ps1`](../tooling/check-agentrouter-public-models.ps1) | Baru — enumerasi roster tanpa auth |
| [`tooling/check-agentrouter-capabilities.ps1`](../tooling/check-agentrouter-capabilities.ps1) | Baru — harness pass@1/avg@3/latency/SE |
| [`docs/BENCHMARK_AND_SCORING.md`](BENCHMARK_AND_SCORING.md) | Baru — definisi + batas epistemik |
| [`docs/evidence/agentrouter-public-models.json`](evidence/agentrouter-public-models.json) | Bukti roster 4 route |
| [`docs/AGENTROUTER_MODEL_VERIFICATION.md`](AGENTROUTER_MODEL_VERIFICATION.md) | Diperbarui → verdict `PARTIALLY RESOLVED` |
| [`tooling/ai-model.ps1`](../tooling/ai-model.ps1) | `deepseek41` dihapus (route tidak ada) |
| [`AI_AGENTS_SETUP.md`](../AI_AGENTS_SETUP.md) | Tabel model diganti dengan roster terverifikasi |

---

## Tindak Lanjut Tahap 13

1. Perbaiki key → jalankan `.\tooling\check-agentrouter-capabilities.ps1` untuk angka **nyata**
2. Baca Usage Log konsol (Step E Tahap 12) untuk atribusi versi
3. Jika `system_fingerprint` muncul, catat — itu pendeteksi pergantian backend diam-diam

**Status pertanyaan versi: SEBAGIAN TERJAWAB** — topologi route tuntas (hanya 1 route
DeepSeek), versi upstream masih tertutup oleh desain gateway (`owner_by` kosong).
