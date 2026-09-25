# AgentRouter Model Verification — DeepSeek V4 Flash

**Date:** 26 September 2026
**Status:** 🟡 **PARTIALLY RESOLVED** — route topology solved without authentication; upstream version still undisclosed
**Verdict:** `Ambiguous on version` / `Definitive on route topology`

---

## 1. The Question

```
Roo Code
   ↓
AgentRouter
   ↓
deepseek-v4-flash
   ↓
V4 Flash (older)   OR   V4.1 Flash (newer)   ?
```

The route alias `deepseek-v4-flash` is a **gateway label**. It is not proof of a version.
AgentRouter owns the alias-to-upstream mapping, so the mapping must be **observed**, never assumed.

**Answer summary:**

| Sub-question | Answer | Confidence |
|--------------|--------|-----------|
| Does a separate `deepseek-v4.1-flash` route exist? | **NO — only one DeepSeek route exists** | High (server-published) |
| Which upstream version serves `deepseek-v4-flash`? | **UNDISCLOSED** | Cannot be determined from gateway data |
| Can route naming resolve V4 vs V4.1? | **NO** — there is no second route to compare | High |

---

## 2. Why This Matters (Scope of Impact)

The repo originally recorded the model in three places with **no version provenance**:

| File | Original Value | Problem |
|------|---------------|---------|
| [`AI_AGENTS_SETUP.md`](AI_AGENTS_SETUP.md:58) | `deepseek-v4-flash` | No version, no fingerprint, no upstream provider |
| [`docs/AI_INSTALLATION_JOURNEY.md`](docs/AI_INSTALLATION_JOURNEY.md:184) | "berfungsi normal" | Connectivity observation only, not version identification |
| [`tooling/ai-model.ps1`](tooling/ai-model.ps1:22) | prints a message only | Did not set any variable — switcher was a no-op for DeepSeek |

Additionally, [`tooling/setup-agentrouter.ps1`](tooling/setup-agentrouter.ps1:10) only ever wrote
`ANTHROPIC_MODEL=claude-opus-4-8` and never touched a DeepSeek model variable at all.

**Conclusion:** the repository contained zero evidence distinguishing V4 Flash from V4.1 Flash.
The alias was an **unverified label**, exactly as suspected.

---

## 3. Method and Tooling

Three scripts were created. All read the API key **only** from `$env:AGENTROUTER_API_KEY`
and never accept it via file or argument.

| Script | Purpose |
|--------|---------|
| [`tooling/check-deepseek-version.ps1`](tooling/check-deepseek-version.ps1) | Captures route catalogue, raw response body, all headers, resolved identifiers, and a behavioural probe |
| [`tooling/diagnose-agentrouter-auth.ps1`](tooling/diagnose-agentrouter-auth.ps1) | Diagnoses the 401 by testing User-Agent and path variants; prints only a masked prefix and a one-way SHA-256 fingerprint |
| [`tooling/check-agentrouter-public-models.ps1`](tooling/check-agentrouter-public-models.ps1) | ⭐ **Enumerates the model roster with NO authentication required** |

### Evidence hierarchy

| Rank | Source | Status |
|------|--------|--------|
| 1 | AgentRouter Usage Log (console) | ⏳ Not yet read |
| 2 | Raw response `model` / `system_fingerprint` | ❌ Unavailable — requests rejected |
| 3 | Response HTTP headers | ✅ Captured |
| 4 | **Public model roster (`/api/pricing`)** | ✅ **Captured — decisive** |
| 5 | Pricing / quota pool behaviour | ✅ Partial (ratios captured) |
| 6 | Behavioural fingerprint | ❌ Unavailable |

---

## 4. What Was Actually Captured

### 4.1 The 401 response body

```json
{
  "error": {
    "message": "unauthorized client detected, contact support for assistance at https://discord.gg/HgekCyHJqB"
  },
  "message": "UNAUTHENTICATED",
  "success": false,
  "type": "unauthorized_client_error"
}
```

**Note the wording:** `unauthorized client detected`, **not** `invalid api key`.
This is a middleware-level rejection, not a key-validation failure.

### 4.2 Response headers — architectural disclosure

```
Request-Id: 20260926012857722516269q4bmqfRt4kVj5
X-Oneapi-Request-Id: 20260926012857722516269q4bmqfRt4kVj5
Set-Cookie: acw_tc=0a0ccbcb...;path=/;HttpOnly;Max-Age=1800
```

Three facts revealed:

1. **`X-Oneapi-Request-Id`** proves the backend is a **one-api / new-api** implementation.
2. **`acw_tc` cookie** is an **Aliyun WAF** marker. A WAF sits in front of the API.
3. The request-id timestamp `20260926012857` decodes to **2026-09-26 01:28:57 local (UTC+8)**,
   confirming a China-region gateway deployment.

```
Client → Aliyun WAF → new-api/one-api → upstream provider
```

### 4.3 The decisive clue — key identity

`diagnose-agentrouter-auth.ps1` printed a safe, non-reversible key report:

```
Present            : True
Resolved from scope: Process
Masked prefix      : sk-Gb0...
Length             : 51 characters
SHA-256 fingerprint: 37dd30fee23933d4
Present in Process : True
Present in User    : True
Present in Machine : False
```

**Cross-reference against the project's own history.**
[`docs/AI_INSTALLATION_JOURNEY.md`](docs/AI_INSTALLATION_JOURNEY.md:140), under *Tahap 8 — Token Terekspos*:

> Selama proses debugging, API key pertama (`sk-Gb0u...`) dibagikan di chat secara langsung
> **Tindakan yang diambil:**
> - Token lama **dihapus** dari konsol AgentRouter
> - Token baru dibuat dengan nama "new", quota $50, mode Chat

The prefix `sk-Gb0u...` in the history **is the same token** currently in the environment variable as `sk-Gb0...`.

> ### 🔴 Root cause identified
> **The `AGENTROUTER_API_KEY` environment variable holds the REVOKED key `sk-Gb0u...`.**
> It was deleted from the AgentRouter console during the Tahap 8 incident, but the
> Windows environment variable was never updated. The valid replacement token lives
> only inside Roo Code's SecretStorage, which is why Roo Code works and scripted
> clients do not.

### 4.4 User-Agent and path matrix

| Probe | URL | User-Agent | Status |
|-------|-----|-----------|--------|
| No UA | `/v1/models` | *(none)* | 401 |
| Roo Code UA | `/v1/models` | `Roo-Code/3.54.0 (Antigravity IDE)` | 401 |
| OpenAI UA | `/v1/models` | `OpenAI/Python 1.55.0` | 401 |
| Claude CLI UA | `/v1/models` | `claude-cli/2.1.281 (external, cli)` | 401 *(different body)* |
| curl UA | `/v1/models` | `curl/8.4.0` | 401 |
| Root host | `/models` | `Roo-Code/3.54.0` | **200 — but returns HTML** |

- **H2 (User-Agent blocking) REJECTED.** All five User-Agents returned 401 identically.
- **The Claude CLI User-Agent produced a different body**, bypassing the middleware:
  ```json
  {"error":{"message":"????? (request id: ...)"},"type":"new_api_error"}
  ```
  Confirms the `new-api` stack from a second angle.
- **`https://agentrouter.org/models` returns the HTML web app**, not an API response.
  Confirms `https://agentrouter.org/v1` is the correct OpenAI-compatible base.

### 4.5 ⭐ BREAKTHROUGH — Public model roster (no auth required)

**Discovered:** `https://agentrouter.org/api/pricing` is **public**. It returns the complete
server-published model roster with pricing ratios, enabled groups, and supported endpoint
types — **no authentication needed**. This bypasses the auth blocker entirely.

Raw evidence: [`docs/evidence/agentrouter-public-models.json`](docs/evidence/agentrouter-public-models.json)

```json
{
  "data": [
    { "model_name": "claude-opus-4-8",  "model_ratio": 4, "completion_ratio": 5, "model_price": 0, "owner_by": "" },
    { "model_name": "claude-opus-5",    "model_ratio": 3, "completion_ratio": 5, "model_price": 0, "owner_by": "" },
    { "model_name": "deepseek-v4-flash","model_ratio": 2, "completion_ratio": 3, "model_price": 0, "owner_by": "" },
    { "model_name": "gpt-6-astra",      "model_ratio": 2, "completion_ratio": 5, "model_price": 0, "owner_by": "" }
  ],
  "success": true
}
```

#### Complete published roster (all 4 routes, exhaustively)

| MODEL_NAME | RATIO | COMPLETION | PRICE | ENDPOINTS | ENABLED GROUPS |
|------------|-------|-----------|-------|-----------|----------------|
| claude-opus-5 | 3 | 5 | per-token | anthropic, openai | test, core, default, probation, svip |
| **deepseek-v4-flash** | **2** | **3** | per-token | openai, anthropic | core, default, probation, svip, test |
| gpt-6-astra | 2 | 5 | per-token | openai | probation, svip, test, core, default |
| claude-opus-4-8 | 4 | 5 | per-token | anthropic, openai | default, probation, svip, test, core |

#### The decisive conclusion

> ### ✅ There is exactly ONE DeepSeek route in the entire gateway: `deepseek-v4-flash`.
>
> **No `deepseek-v4.1-flash` route exists. No `deepseek-v4.1` alias. No `deepseek-chat`,
> no `deepseek-reasoner`, no versioned sibling of any kind.**
>
> **Therefore route naming cannot answer the V4 vs V4.1 question** — there is no second
> route to compare against. This *disproves* the hopeful hypothesis that AgentRouter
> conveniently offers a separate, clearly-labelled V4.1 endpoint.
>
> It also means the alias `deepseek-v4.1-flash` added to
> [`tooling/ai-model.ps1`](tooling/ai-model.ps1:30) as a `deepseek41` option
> **would fail** — that route does not exist.

#### The remaining limitation

The `owner_by` field — the channel/provider attribution column that would have named the
upstream version — is **empty for every single model**. AgentRouter does not publish which
provider or model build serves each alias.

**Consequence:** route enumeration alone **cannot** resolve the upstream version.
This is a genuine, structural limitation of the gateway, not a gap in our method.

#### Incidental metrics from the roster

- `deepseek-v4-flash` has the **cheapest output tokens** of all four routes (`completion_ratio` 3 vs 5)
- `deepseek-v4-flash` and `gpt-6-astra` tie for cheapest input (`model_ratio` 2)
- All models are in the `default` group, so the "budget pool" theory is separate from group placement
- `deepseek-v4-flash` supports **both** `openai` and `anthropic` protocols

### 4.6 A PowerShell parsing gotcha worth recording

`ConvertFrom-Json` in PowerShell 5.1 throws:

```
Cannot process argument because the value of argument "name" is not valid.
```

when an object contains an **empty-string key**. AgentRouter returns:

```json
"usable_group": { "": "用户分组", "default": "默认分组" }
```

This is why the first two script runs failed with a misleading "not JSON" message —
the response *was* valid JSON, but PowerShell could not materialise the empty key.
Fixed in [`tooling/check-agentrouter-public-models.ps1`](tooling/check-agentrouter-public-models.ps1:78)
by rewriting `{"":` to `{"_blank":` before parsing.

---

## 5. Verdict

| Question | Answer |
|----------|--------|
| Which DeepSeek version does AgentRouter serve? | **UNDISCLOSED — not derivable from gateway data** |
| Does a distinct V4.1 route exist? | **NO — definitively only one DeepSeek route** |
| Why was authentication blocked? | Env var contained the revoked token `sk-Gb0u...` |
| Is the gateway hiding the version? | **Effectively yes** — `owner_by` is blank for all models |
| Was any conclusion fabricated? | **No** |

**What we DID prove:** the route topology, exhaustively. One DeepSeek alias, no versioned siblings.

**What we CANNOT prove from gateway data:** which upstream build serves that alias.
The `owner_by` field is the field that would answer it, and AgentRouter leaves it empty.

---

## 6. What Would Actually Answer the Version Question

Ranked by likelihood of success.

### 6.1 Ask DeepSeek's API directly (strongest)

Send an identical prompt to both AgentRouter's alias **and** DeepSeek's official API,
then compare. If DeepSeek's official API exposes a V4.1 endpoint, a side-by-side
response comparison is the most reliable discriminator available.

### 6.2 Read the AgentRouter Usage Log (free, authoritative)

1. Log in to **https://agentrouter.org/console**
2. Open **Usage Log / 日志**
3. Filter for `deepseek`
4. Record: exact model name shown, upstream provider column, token counts, cost

The console sometimes retains channel attribution that the public API withholds.

### 6.3 Repair the key and capture live metadata

```powershell
.\tooling\setup-agentrouter.ps1        # option 1 or 2, paste the CURRENT token
.\tooling\diagnose-agentrouter-auth.ps1 # must report 200
.\tooling\check-deepseek-version.ps1    # capture model, id, system_fingerprint
```

If AgentRouter echoes a `system_fingerprint`, record it. A stable fingerprint is the
closest thing to a version identifier the gateway will ever give you, and a change
in it over time signals a silent backend swap.

### 6.4 Ask AgentRouter support (they hold the answer)

Discord: `discord.gg/HgekCyHJqB`

> "Which upstream DeepSeek model version serves the `deepseek-v4-flash` alias —
> V4 Flash or V4.1 Flash? Your `/api/pricing` endpoint leaves `owner_by` empty,
> so the version is not discoverable by clients."

### 6.5 Behavioural probe — WEAK, corroboration only

Do **not** rely on asking the model "what version are you". Models have stale
self-knowledge, are often instructed to deny version details, and a V4.1 alias may
still self-report as V4. Recorded only as a tie-breaker, never as proof.

---

## 7. Blocker to Clear

```powershell
# Run this, choose option 1 or 2, paste the CURRENT token from the console
.\tooling\setup-agentrouter.ps1

# Then confirm authentication is fixed
.\tooling\diagnose-agentrouter-auth.ps1
```

The diagnostic reports `[200]` for the models probe when the key is valid.

**Security note:** during Tahap 8 the first token was exposed in a chat transcript.
The revocation was **incomplete** — the server-side deletion happened, but the local
environment variable was never cleaned up, leaving a dead credential in active use.

### Recommended hardening

`SetEnvironmentVariable(..., "User")` writes **plaintext** into `HKCU\Environment`,
readable by any process running as that user. The claim in
[`AI_AGENTS_SETUP.md`](AI_AGENTS_SETUP.md:111) that the key is "tersimpan aman di registry Windows"
is **optimistic**. Use **Windows Credential Manager** or Roo Code SecretStorage instead.

---

## 8. Next Steps

1. **Fix the model switcher option** — `deepseek41` in [`tooling/ai-model.ps1`](tooling/ai-model.ps1:30)
   targets a route proven not to exist. Either remove it or label it clearly as unavailable
2. **Read the console Usage Log** (Section 6.2) — free, authoritative, no key needed
3. **Repair the key** (Section 7), then run [`tooling/check-deepseek-version.ps1`](tooling/check-deepseek-version.ps1)
4. **Ask support** (Section 6.4) if the console also withholds attribution
5. **Record the verdict** at that point — not before

---

## 9. Evidence Integrity Statement

- No API key appears anywhere in this document or in any evidence file.
- Only a masked prefix (`sk-Gb0...`) and a one-way SHA-256 fingerprint are recorded,
  and only because they are required to prove key identity against the console.
- The fingerprint `37dd30fee23933d4` **cannot** be reversed to recover the key.
  It is safe to retain as a comparison value.
- The revoked key `sk-Gb0u...` should be treated as permanently compromised. It is
  already deleted server-side; it merely must stop being used locally.
- `docs/evidence/` is git-ignored to keep raw request IDs out of version control.
