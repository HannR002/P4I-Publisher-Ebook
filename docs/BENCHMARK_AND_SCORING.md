# Benchmark & Scoring — Definisi, dan Apa yang Benar-Benar Bisa Kita Ukur

**Tanggal:** 26 September 2026
**Model aktif:** `deepseek-v4-flash` (via AgentRouter)
**Status pengukuran:** ❌ **Belum terukur** — diblokir oleh kredensial (401), lihat bagian 4

---

## 1. Mengapa dokumen ini ada

Pertanyaan yang diajukan: *"bisakah anda mengecek [tabel terminologi benchmark] model yang kita gunakan sekarang?"*

Jawaban jujurnya punya dua lapis:

1. **Terminologinya** bisa dijelaskan dengan tepat (bagian 2).
2. **Angkanya** untuk model kita **tidak bisa dikutip** — dan bukan karena kita malas, tapi karena
   secara struktural tidak ada (bagian 3), plus saat ini sedang diblokir kredensial (bagian 4).

Yang **bisa** kita kerjakan sudah dikerjakan: sebuah *harness* yang mengukur metrik ini secara
langsung dari model. Harness-nya jalan; hasilnya menunggu key diperbaiki (bagian 5).

---

## 2. Tabel Terminologi — Definisi & Rumus

| Istilah | Arti | Rumus / Catatan |
|---------|------|-----------------|
| **Accuracy / Score %** | Persentase jawaban benar dari seluruh soal | `benar / total × 100%`. Di benchmark LLM biasanya berupa **pass rate**, bukan akurasi statistik. |
| **pass@1** | Probabilitas benar pada **satu** percobaan pertama, tanpa retry | `benar_1_run / total_soal`. Ini yang dikutip sebagai "skor" model. |
| **pass@k** | Probabilitas benar **minimal sekali** dalam *k* percobaan | Estimasi tak-bias: `1 − C(n−c, k) / C(n, k)`, dengan *n* = sampel, *c* = yang benar. Naik cepat; **k=1 adalah satu-satunya yang jujur untuk perbandingan**. |
| **avg@k** | Rata-rata skor di seluruh *k* percobaan (bukan "minimal sekali") | `(1/k) Σ skor_i`. Ukur **konsistensi**, bukan puncak. Lebih rendah dari pass@k pada task yang sama. |
| **Elo** | Skor peringkat relatif dari duel berpasangan | Menang → +Δ, kalah → −Δ, Δ bergantung selisih Elo. **Butuh arena manusia/model.** Tidak bisa dihitung dari 5 soal. |
| **Harness** | Perangkat lunak yang menjalankan model terhadap test set dan menilai jawabannya | Contoh publik: SWE-bench, HumanEval. Harness menentukan prompt, scorer, jumlah sampel. **Beda harness = beda angka.** |
| **Reasoning effort** | Anggaran token/"berpikir" yang dialokasikan sebelum menjawab | `low / medium / high`. Semakin tinggi → akurasi naik, latency & biaya ikut naik. **Harus disebutkan**; tanpa itu angka tidak reproducible. |
| **With tools** | Skor saat model boleh memanggil tool (kalkulator, shell, retriever) | Biasanya lebih tinggi dari *without tools*. **Jangan campur** keduanya dalam satu tabel. |
| **Strict / partial** | Cara penilaian jawaban | **Strict** = harus persis (mis. regex full-match). **Partial** = cukup sebagian token kunci muncul. Partial selalu ≥ strict. |
| **Standard error (SE)** | Ketidakpastian pada skor rata-rata | `SD / √n`. n kecil → SE besar. Tanpa SE, selisih 2% antar model **tidak bermakna**. |

### Aturan baca tabel benchmark

> Jika sebuah tabel tidak menyebut **(a)** nama harness, **(b)** reasoning effort, **(c)** with/without
> tools, dan **(d)** jumlah sampel *n*, maka angka itu **tidak bisa dibandingkan** dengan tabel lain.

---

## 3. Yang TIDAK Bisa Kita Ketahui (Batas Epistemik)

Ini temuan paling penting, dan sudah diverifikasi lewat [`docs/AGENTROUTER_MODEL_VERIFICATION.md`](AGENTROUTER_MODEL_VERIFICATION.md).

| Metrik yang diminta | Tersedia dari AgentRouter? | Alasan |
|---------------------|---------------------------|--------|
| Accuracy / Score % | ❌ | Gateway **tidak menerbitkan benchmark apa pun** |
| pass@1 | ❌ | Tidak ada test set, tidak ada scorer |
| avg@3 / pass@k | ❌ | Tidak ada harness di sisi gateway |
| Elo | ❌ | Butuh arena; gateway hanya proxy, bukan penyelenggara arena |
| Harness | ❌ | Tidak dispesifikasikan |
| Reasoning effort | ❌ | Tidak diekspos di `/api/pricing` |
| With tools | ⚠️ Parsial | Hanya daftar protokol (`openai`, `anthropic`) — bukan skor |
| Strict / partial | ❌ | Tidak ada |
| Standard error | ❌ | Tidak ada *n* |

### Kenapa angkanya tidak boleh "dipinjam" dari DeepSeek

Route `deepseek-v4-flash` adalah **alias milik gateway**, bukan identitas build.

- Field `owner_by` **kosong untuk SEMUA model** (lihat [`docs/evidence/agentrouter-public-models.json`](evidence/agentrouter-public-models.json)).
- Artinya AgentRouter **tidak mengungkap** build upstream mana yang melayani alias itu.
- Benchmark resmi DeepSeek dipublikasikan untuk **nama model di situs DeepSeek**, bukan untuk
  build spesifik yang kebetulan duduk di belakang alias AgentRouter.

> **Kesimpulan:** mengutip angka benchmark DeepSeek resmi sebagai "skor `deepseek-v4-flash` di AgentRouter"
> adalah **kesalahan atribusi**. Itu mengukur label, bukan build yang terverifikasi. Kita menolak melakukannya.

### Satu hal kecil yang MEMANG terukur dari gateway

Dari roster publik, kita dapat metrik **biaya**, dan itu fakta keras:

| Model | model_ratio (input) | completion_ratio (output) | Protokol |
|-------|--------------------|--------------------------|----------|
| claude-opus-4-8 | 4 | 5 | anthropic, openai |
| claude-opus-5 | 3 | 5 | anthropic, openai |
| **deepseek-v4-flash** | **2** | **3** | openai, anthropic |
| gpt-6-astra | 2 | 5 | openai |

`deepseek-v4-flash` punya **biaya output termurah** (3, vs 5 milik model lain) dan seri termurah untuk input.
Ini *price-performance*, bukan *capability score*.

---

## 4. Status Pengukuran Langsung (Jujur)

Harness [`tooling/check-agentrouter-capabilities.ps1`](../tooling/check-agentrouter-capabilities.ps1) telah dibuat
dan **dijalankan**, tapi **semua 15 panggilan ditolak HTTP 401**.

```
 Model  : deepseek-v4-flash
 Runs   : 3 per task (avg@3)
 Tasks  : 5
 Calls  : 15

 Valid calls        : 0/15
 pass@1 (strict)    : 0/15 = 0
 avg@3 (partial)   : 0
 Standard error     : 0
```

**Angka 0 itu TIDAK berarti modelnya bodoh.** Artinya kredensial ditolak sebelum sempat menilai apa pun.
Menyajikan angka ini sebagai "skor" justru contoh buruk yang dilarang di bagian 2.

**Root cause (sudah terbukti):** environment variable `AGENTROUTER_API_KEY` masih menyimpan
token yang **sudah dicabut** (`sk-Gb0u...`, dihapus dari konsol pada Tahap 8) — sementara Roo Code
punya salinan valid di SecretStorage-nya sendiri. Bukti: [`docs/AGENTROUTER_MODEL_VERIFICATION.md`](AGENTROUTER_MODEL_VERIFICATION.md) bagian 4.3.

Artefak: `docs/evidence/agentrouter-capabilities-20260926-010638/`

---

## 5. Cara Menjalankan (setelah key diperbaiki)

```powershell
# 1. Perbaiki kredensial (paste token AKTIF dari konsol)
.\tooling\setup-agentrouter.ps1

# 2. Pastikan sudah 200, bukan 401
.\tooling\diagnose-agentrouter-auth.ps1

# 3. Ukur kapabilitas — pass@1 dan avg@3 sekaligus
.\tooling\check-agentrouter-capabilities.ps1

# Variasi:
.\tooling\check-agentrouter-capabilities.ps1 -Runs 1        # pass@1 saja, lebih murah
.\tooling\check-agentrouter-capabilities.ps1 -Model gpt-6-astra
```

Output: `docs/evidence/agentrouter-capabilities-<stamp>/capabilities-summary.json`

### Apa yang diukur harness ini

| Metrik | Implementasi |
|--------|--------------|
| pass@1 | 1 panggilan per task, hitung strict-pass / total |
| avg@3 | 3 panggilan per task, rata-rata skor per-task |
| strict vs partial | regex full-match (1.0) vs token-kunci hadir (0.5) vs gagal (0.0) |
| latency | wall-clock per panggilan → median + p95 |
| with tools | `tool_calls` dihitung bila model memancarkannya |
| standard error | `SD / √n` atas skor per-run |

### Apa yang **tidak** diklaim harness ini

- ❌ Bukan benchmark terstandar. 5 task ≠ HumanEval/SWE-bench.
- ❌ Tidak melaporkan Elo (butuh arena).
- ❌ Nilainya **kualitatif**. `caveat` ditulis eksplisit di dalam JSON-nya.
- ✅ Menyimpan `resolved_model` dan `system_fingerprint` bila gateway mengembalikannya —
      ini yang bisa mendeteksi **pergantian backend diam-diam**.

---

## 6. Ringkasan Satu Paragraf

Model yang kita pakai sekarang adalah **`deepseek-v4-flash`**, dan itu satu-satunya route DeepSeek
yang ada di AgentRouter (terbukti dari `/api/pricing`). Gateway **tidak menerbitkan** Accuracy, pass@1,
avg@3, Elo, maupun spesifikasi harness untuk route mana pun, dan field `owner_by` kosong sehingga versi
upstream-nya pun tertutup — jadi **tidak ada satu angka benchmark pun yang sah untuk kita kutip**.
Yang tersedia hanyalah fakta biaya (input ratio 2, output ratio 3 — termurah di roster). Untuk mendapatkan
angka nyata, kita harus **mengukurnya sendiri**: harness sudah ditulis dan siap; ia hanya menunggu
environment key yang dicabut diperbaiki, setelah itu `pass@1`, `avg@3`, latency, dan standard error
akan terisi dengan data nyata — bukan kutipan.
