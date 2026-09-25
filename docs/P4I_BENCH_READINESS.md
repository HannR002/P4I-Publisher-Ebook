# P4I-Bench v1 Readiness Status

**Date:** 26 September 2026
**Status:** `READY_FOR_CONTROLLED_PILOT`

---

## 1. Validation Gates

| Gate | Status | Notes |
|------|--------|-------|
| 15 tasks verified | [x] PASS | All 15 tasks mapped to actual repository paths (`docs/P4I_BENCH_TASK_MANIFEST.md`). |
| worktree isolation works | [x] PASS | `p4i-bench.ps1` successfully provisions and tears down `.bench/worktrees/` |
| production tree untouched | [x] PASS | Script tests `git status --porcelain` before and after execution to ensure safety. |
| deterministic mock | [x] PASS | `-Mode Mock` bypassed runtime randomization, instead using fixed fixture data (`tooling/fixtures/p4i-bench-mock-results.json`). |
| no eval arbitrary code | [x] PASS | `tooling/check-agentrouter-capabilities.ps1` now uses `ast.parse` and explicitly denied builtin variables (`__builtins__: None`) in a temp dir. |
| secret deny-list tested | [x] PASS | `Test-ContextSecurity` explicitly rejects `.env`, `*.pem`, `credentials*`, etc. |
| candidate blinding works | [x] PASS | Output metrics are mapped to Candidate A/B. Real mapping is inside `docs/evidence/p4i-bench/private/candidate-map.json` which is `.gitignore`'d. |
| pass@1 denominator fixed | [x] PASS | `check-agentrouter-capabilities.ps1` updated to only use valid 1st attempts as denominator. |
| cleanup tested | [x] PASS | Worktree forces teardown and cleans up temp scripts on completion/abort. |
| Live requires ConfirmLive | [x] PASS | Script throws `ABORT` and exits `1` if `-Mode Live` is run without `-ConfirmLive`. |
| no API request occurred during validation | [x] PASS | Validated. All runs were in Mock or Validate mode; no billable quota consumed. |

---

## 2. Next Steps

The infrastructure is verified safe and reproducible.

### Proceed to Controlled Pilot
We recommend executing a 1x2x1 pilot (1 task, 2 candidates, 1 run) to test the end-to-end pipeline before running all 15 tasks.

**Do NOT run the full benchmark yet.**
