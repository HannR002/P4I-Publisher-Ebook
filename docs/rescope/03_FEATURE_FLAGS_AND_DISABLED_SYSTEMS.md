# Feature Flags and Disabled Systems

Configuration lives in `config/features.php`; Blade and application code use `config()`, never direct `env()` calls.

```dotenv
MIDTRANS_ENABLED=false
ROYALTY_ENABLED=false
PAYOUT_ENABLED=false
AUTHOR_KYC_ENABLED=false
```

All defaults are `false`.

## Enforcement

- Midtrans: legacy checkout, token refresh page, success/pending endpoints, and webhook routes use `feature:midtrans`; controllers/jobs also short-circuit. Snap JS loads only when enabled.
- Royalty: `RecordAuthorRoyaltyListener` returns without writing when disabled. Duplicate manual listener registration was removed; Laravel 12 auto-discovery remains.
- Payout: author/admin routes use `feature:payout`; `PayoutService` also rejects request/complete/reject operations when disabled.
- Author KYC: admin/status routes and navigation are guarded/hidden. Registration rules and storage do not request KTP/bank data. The existing non-null encrypted legacy ID field receives an empty value only to preserve the deployed schema. Submission authorization and verified-author middleware bypass financial KYC when disabled.

Disabled HTTP features intentionally return 404 rather than an unsafe 500. Historical tables/files are preserved and not exposed.

`.env.testing` enables legacy flags only to retain coverage of the pre-rescope test suite. New tests override each flag to prove disabled behavior. Production must keep all four false for this release.
