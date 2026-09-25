# P4I-Bench v1 Specification

**Date:** 26 September 2026
**Purpose:** Internal benchmark for evaluating AI models on real tasks from the P4I Publisher Ebook repository. Stop chasing hype and measure what actually matters to our production context.

---

## 1. Core Principles

- **No Production Risks:** Business logic in production must never be modified by the benchmark. All evaluations run in a disposable git worktree or temporary snapshot.
- **Zero Secrets:** No `.env`, API keys, SSH keys, database credentials, hosting credentials, or any secrets are accessed, sent, or evaluated.
- **No Upstream Identity Assumptions:** Alias `deepseek-v4-flash` via AgentRouter is recorded as the gateway alias. It is explicitly NOT assumed to be DeepSeek V4.1. Only verified route names are used.
- **Reproducibility:** All results must be reproducible.

---

## 2. Benchmark Tasks (15 Real P4I Tasks)

The benchmark consists of 15 tasks sampled from the P4I repository across the following categories:

1. **Bug-fix (3 tasks)**
2. **Feature/Change (Small) (3 tasks)**
3. **Refactoring (2 tasks)**
4. **Test-Generation (2 tasks)**
5. **Security/Code-Quality (2 tasks)**
6. **Database/Migration Reasoning (1 task)**
7. **Frontend/Blade (1 task)**
8. **Documentation/Architecture Comprehension (1 task)**

*(Note: Production bugs are never introduced permanently. Disposable snapshots are used.)*

---

## 3. Metrics

For each task, the following metrics are measured and recorded:

- `task_success` (Boolean)
- `tests_passed` (Integer)
- `tests_failed` (Integer)
- `regressions` (Boolean/Integer)
- `latency_seconds` (Float)
- `tool_calls` (Integer)
- `files_modified` (Integer)
- `lines_added` (Integer)
- `lines_removed` (Integer)
- `unnecessary_file_changes` (Integer)
- `retries` (Integer)
- `human_interventions` (Integer)
- `execution_errors` (Integer)
- `API/provider_errors` (Integer)

**Gateway Metadata:**
If the provider returns `resolved_model` and `system_fingerprint`, they are recorded. No secrets or sensitive prompts are logged.

---

## 4. Scoring

The evaluation generates two classes of results:

### 4.1. Hard Metrics
- Test pass/fail
- Success criteria completion
- Regressions introduced
- Latency (seconds)
- Retries required
- File churn (lines/files modified)

### 4.2. Human Review
Evaluated on a scale of 1-5 for:
- Correctness
- Maintainability
- Minimality
- Security
- Architecture Consistency

**Anti-pattern:** We do NOT aggregate these into a single "AI IQ" number.

---

## 5. Blind Comparison & Novelty Bias Reduction

Results are stored anonymously:
- **Candidate A**
- **Candidate B**

Provider and model names are completely redacted during the scoring and human review phase. The mapping is unsealed only after scoring is finalized. This effectively mitigates novelty/hype bias.

---

## 6. Model Comparison

**Baseline:**
- Gemini 3.1 Pro via Antigravity

**Challengers:**
- deepseek-v4-flash via Roo Code / AgentRouter (Identity unassumed)
- *(If Claude/Astra quotas become available later, they will be added as challengers.)*

---

## 7. Execution

The primary execution script is `tooling/p4i-bench.ps1`.
Output evidence and logs are stored in `docs/evidence/p4i-bench/`.
