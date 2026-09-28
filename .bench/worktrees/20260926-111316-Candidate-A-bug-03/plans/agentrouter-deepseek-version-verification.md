# Plan: Verify Which DeepSeek V4 Flash Version AgentRouter Actually Serves

## The Question Being Answered

```
Roo Code
   |
   v
AgentRouter
   |
   v
deepseek-v4-flash
   |
   +--> DeepSeek V4 Flash  (older route)      ?
   |
   +--> DeepSeek V4.1 Flash (newer route)     ?
```

The route name `deepseek-v4-flash` is a **gateway alias**, not a version guarantee.
AgentRouter owns the mapping. We must observe it, not assume it.

## Core Principle

> The only trustworthy sources of truth are:
> 1. Response metadata returned by AgentRouter (body + headers)
> 2. AgentRouter's own usage/billing log
>
> Everything else (docs, marketing, naming intuition) is a hypothesis.

## Evidence Hierarchy

| Rank | Evidence Source | Strength | Why |
|------|-----------------|----------|-----|
| 1 | AgentRouter Usage Log (console) | Strongest | Server-side record of what was actually billed/executed |
| 2 | Raw response `model` / `system_fingerprint` field | Strong | Gateway echo of resolved upstream model |
| 3 | Raw response HTTP headers (`x-*`) | Strong if present | Often carries routing/provider/version info |
| 4 | `GET /v1/models` route enumeration | Medium | Reveals if a distinct V4.1 route exists at all |
| 5 | Pricing/quota pool behavior differences | Medium | Different versions rarely share identical billing |
| 6 | Behavioral fingerprinting (cutoff, style) | Weak | Corroboration only, never primary proof |

## Important Constraint

The API key currently lives only in Windows USER environment variables and Roo Code
SecretStorage. The verification script **must not** write the key to any file,
log, or clipboard, and must not accept it as a plaintext CLI argument.

`$env:AGENTROUTER_API_KEY` is the only permitted input channel.

## Why Requesting `system_fingerprint` Matters

Many gateways attach a stable fingerprint per upstream model build. If AgentRouter
returns one, a change in that value across time indicates a backend model swap even
when the alias name stays identical.

## Behavioral Probe Caveat

Asking the model "what version are you" is unreliable. Models frequently have stale
self-knowledge or are instructed to deny version details. Use it only as a
tie-breaker, and record it as low-confidence evidence.

## Deliverable

A short evidence report at `docs/AGENTROUTER_MODEL_VERIFICATION.md` containing
raw captured metadata (redacted of the API key) plus an explicit verdict:

- `V4 Flash (older)` — with supporting evidence
- `V4.1 Flash (newer)` — with supporting evidence
- `Ambiguous` — and the exact next action required

## Absolute Rules

- No level-of-effort estimates in this plan, by design.
- API key never enters a repository file.
- If the verdict is Ambiguous, do NOT fabricate a conclusion.
  Escalate to AgentRouter support with the captured evidence instead.
