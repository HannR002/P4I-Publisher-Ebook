# P4I-Bench v1 Results

**Date:** 26 September 2026
**Status:** ⏳ **Awaiting Approval for Live Run** (Currently showing MOCK/DRY-RUN outputs)

---

## 1. Overview

This document tracks the results of evaluating AI candidates against the internal **P4I-Bench v1** specification.

- **Baseline:** Gemini 3.1 Pro (via Antigravity)
- **Challenger:** `deepseek-v4-flash` (via AgentRouter)

*Note: All results below are generated via the dry-run mock infrastructure (`tooling/p4i-bench.ps1`). Live execution requires manual approval to consume API quota.*

---

## 2. Hard Metrics (Mock Data)

| Task ID | Category | Candidate | Success | Regressions | Latency (s) | Tools Used | Files Mod |
|---------|----------|-----------|---------|-------------|-------------|------------|-----------|
| bug-01 | bug-fix | Candidate A | TRUE | 0 | 2.34 | 0 | 1 |
| bug-01 | bug-fix | Candidate B | TRUE | 0 | 1.89 | 0 | 1 |
| feat-01 | feature | Candidate A | TRUE | 0 | 4.12 | 0 | 1 |
| feat-01 | feature | Candidate B | TRUE | 0 | 3.45 | 0 | 1 |
| ... | ... | ... | ... | ... | ... | ... | ... |

*(Full mock dataset is available in `docs/evidence/p4i-bench/run-*/mock-results.json`)*

---

## 3. Human Review Scores (Pending)

| Task Category | Candidate A | Candidate B |
|---------------|-------------|-------------|
| Correctness | TBD | TBD |
| Maintainability | TBD | TBD |
| Minimality | TBD | TBD |
| Security | TBD | TBD |
| Arch Consistency| TBD | TBD |

---

## 4. Unsealing (Pending)

- **Candidate A:** [SEALED]
- **Candidate B:** [SEALED]

*To be unsealed only after Human Review is complete.*
