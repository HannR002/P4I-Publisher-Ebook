# 06 — PRODUCTION READINESS CHECKLIST

**Audit Date:** 2026-09-21

---

## Legend

| Status | Meaning |
|---|---|
| ✅ READY | Production-ready as-is |
| ⚙️ NEEDS CONFIG | Requires configuration, not code change |
| 🔧 NEEDS CODE | Requires code changes |
| ⛔ BLOCKER | Must fix before any production deployment |

---

## 1. Environment & Configuration

| Item | Status | Evidence | Action |
|---|---|---|---|
| APP_ENV | ⚙️ NEEDS CONFIG | Must be `production` | Set in .env |
| APP_DEBUG | ⚙️ NEEDS CONFIG | Must be `false` | Set in .env |
| APP_KEY | ⚙️ NEEDS CONFIG | Must be generated and kept secret | Verify set, never expose |
| APP_URL | ⚙️ NEEDS CONFIG | Must be production URL | Set in .env |
| HTTPS | 🔧 NEEDS CODE | No `URL::forceScheme('https')` in AppServiceProvider | Add HTTPS enforcement |
| HSTS header | 🔧 NEEDS CODE | No Strict-Transport-Security header | Add to middleware or server config |

## 2. Database

| Item | Status | Evidence | Action |
|---|---|---|---|
| DB Driver | ⚙️ NEEDS CONFIG | Currently SQLite in dev, MySQL config in database.php | Configure MySQL for production |
| Cascade deletes | ⛔ BLOCKER | `orders.user_id`, `order_items.book_id`, `book_licenses.user_id` cascade delete | Change to `restrict` + soft deletes |
| Missing constraints | 🔧 NEEDS CODE | ISBN not unique, no status guards | Add constraints |
| Migration sync | 🔧 NEEDS CODE | `submission_reviews.notes` column missing | Add migration or remove code |
| DB Backup | ⚙️ NEEDS CONFIG | No backup strategy | Configure automated backups |
| Connection pooling | ⚙️ NEEDS CONFIG | Default connection config | Tune for production load |

## 3. Payment (Midtrans)

| Item | Status | Evidence | Action |
|---|---|---|---|
| Midtrans Mode | ⚙️ NEEDS CONFIG | `MIDTRANS_IS_PRODUCTION` defaults to false | Set true for production |
| Server Key | ⚙️ NEEDS CONFIG | Read from env | Ensure production key set |
| Client Key | ⚙️ NEEDS CONFIG | Read from env | Ensure production key set |
| Webhook URL | ⚙️ NEEDS CONFIG | Configure in Midtrans dashboard | Set production URL |
| IP Whitelist | ✅ READY | `CheckMidtransIp` middleware active in production | — |
| Signature Validation | ✅ READY | SHA-512 signature check | — |
| Replay Protection | 🔧 NEEDS CODE | 5-min cache TTL too short | Increase TTL or use DB |
| Amount Verification | 🔧 NEEDS CODE | Not cross-checking webhook amount vs order | Add verification |
| Refund Handling | 🔧 NEEDS CODE | No refund status or handler | Add refund flow |
| CSRF Exclusion | ✅ READY | Webhook excluded from CSRF in bootstrap/app.php | — |

## 4. Authentication & Session

| Item | Status | Evidence | Action |
|---|---|---|---|
| Breeze Auth | ✅ READY | Standard email/password auth | — |
| Email Verification | 🔧 NEEDS CODE | `MustVerifyEmail` commented out in User.php | Uncomment to require email verification |
| Password Hashing | ✅ READY | bcrypt via `'password' => 'hashed'` cast | — |
| Session Driver | ⚙️ NEEDS CONFIG | `SESSION_DRIVER` needs to be set (database or redis) | Configure for production |
| Session Lifetime | ⚙️ NEEDS CONFIG | Default 120 minutes | Review and set appropriate lifetime |
| CSRF Protection | ✅ READY | Enabled globally, webhook excluded | — |
| Rate Limiting | 🟡 PARTIAL | Only on checkout (throttle:10,1) and login (5 attempts) | Add to other sensitive endpoints |
| Account Suspension | ✅ READY | `EnsureAccountIsActive` middleware on all web routes | — |

## 5. Queue & Jobs

| Item | Status | Evidence | Action |
|---|---|---|---|
| Queue Driver | ⚙️ NEEDS CONFIG | Must be `database` or `redis` (not `sync`) | Configure in .env |
| Queue Worker | ⚙️ NEEDS CONFIG | Must run `php artisan queue:work` as daemon | Set up supervisor/systemd |
| Failed Jobs | ✅ READY | `failed_jobs` table exists in migration | — |
| Job Retries | ⚙️ NEEDS CONFIG | `queue:listen --tries=1` in dev script | Configure appropriate retries |
| Job Timeout | ⚙️ NEEDS CONFIG | `--timeout=0` in dev script | Set reasonable timeout |
| Webhook Job | ✅ READY | `ProcessMidtransWebhook` is queued | — |
| Listener Jobs | ✅ READY | Royalty listener is queued | — |

## 6. Mail

| Item | Status | Evidence | Action |
|---|---|---|---|
| Mail Driver | ⚙️ NEEDS CONFIG | Must be SMTP or mail service (not `log`) | Configure in .env |
| From Address | ⚙️ NEEDS CONFIG | Set in .env | — |
| Purchase Email | ⛔ BLOCKER | Listener NOT registered in EventServiceProvider | Register listener |
| Email Template | ✅ READY | `purchase_confirmation.blade.php` exists | — |

## 7. Storage

| Item | Status | Evidence | Action |
|---|---|---|---|
| Local Disk | ✅ READY | PDFs stored in `storage/app/private/` | — |
| Public Disk | ⚙️ NEEDS CONFIG | Requires `storage:link` symlink | Create symlink on server |
| Cover Images | ✅ READY | Stored in `public` disk | — |
| KTP Documents | ✅ READY | Stored in `private/kyc` | — |
| Manuscripts | ✅ READY | Stored in `private/submissions` | — |
| File Size Limits | ✅ READY | PDF max 50MB, images max 5MB | — |
| CDN | ❌ NOT CONFIGURED | No CDN for static assets | Consider for production |

## 8. Logging & Monitoring

| Item | Status | Evidence | Action |
|---|---|---|---|
| Log Channel | ⚙️ NEEDS CONFIG | Uses `single` channel explicitly in webhook | Configure stack/daily for production |
| Security Logging | ✅ READY | Admin actions, KYC, license changes logged | — |
| Error Tracking | ❌ NOT CONFIGURED | No Sentry, Bugsnag, etc. | Add error tracking service |
| Performance Monitoring | ❌ NOT CONFIGURED | No APM | Consider Laravel Telescope or external APM |
| Health Check | ✅ READY | `/up` health endpoint configured | — |

## 9. Cache

| Item | Status | Evidence | Action |
|---|---|---|---|
| Cache Driver | ⚙️ NEEDS CONFIG | Must be `redis` or `database` (not `array`) | Configure in .env |
| Webhook Dedup | 🔧 NEEDS CODE | Uses cache — fragile with `array` driver | Ensure persistent cache driver |
| Config Cache | ⚙️ NEEDS CONFIG | `php artisan config:cache` in deploy script | — |
| Route Cache | ⚙️ NEEDS CONFIG | `php artisan route:cache` in deploy script | — |
| View Cache | ⚙️ NEEDS CONFIG | `php artisan view:cache` in deploy script | — |

## 10. Security

| Item | Status | Evidence | Action |
|---|---|---|---|
| SQL Injection | ✅ READY | Eloquent ORM used throughout | — |
| XSS | ✅ READY | Blade `{{ }}` auto-escaping | — |
| CSRF | ✅ READY | Enabled globally | — |
| Mass Assignment | ✅ READY | `$fillable` defined on all models | — |
| File Upload Validation | ✅ READY | MIME type + magic bytes validation | — |
| Encrypted Fields | 🟡 PARTIAL | `id_card_number` and `snap_token` encrypted; bank data NOT encrypted | Encrypt bank data |
| HTTP Headers | 🔧 NEEDS CODE | No X-Frame-Options, X-Content-Type-Options, CSP | Add security headers |
| CORS | ⚙️ NEEDS CONFIG | Default config — review if API added | — |
| Sensitive Files | ⛔ BLOCKER | `ngrok.exe` and `p4i_publisher.sql` in repo root | Remove from version control |

## 11. Deployment

| Item | Status | Evidence | Action |
|---|---|---|---|
| Deploy Script | ✅ READY | `server_deploy.sh` exists | — |
| Composer Prod | ✅ READY | `--no-dev` flag in deploy script | — |
| Asset Build | ⚙️ NEEDS CONFIG | `npm run build` not in deploy script | Add to deployment pipeline |
| Symlink | ⚙️ NEEDS CONFIG | Manual symlink instruction in deploy script | — |
| Zero-Downtime | ❌ NOT CONFIGURED | No zero-downtime deployment strategy | Consider Envoyer or similar |
| CI/CD | ❌ NOT CONFIGURED | No GitHub Actions, GitLab CI, etc. | Set up CI/CD pipeline |

## 12. Scheduler

| Item | Status | Evidence | Action |
|---|---|---|---|
| Cron Jobs | ❌ NOT CONFIGURED | `console.php` only has `inspire` command | — |
| Stale Orders | 🔧 NEEDS CODE | Pending orders never expire automatically | Add scheduled command to expire stale orders |
| Report Generation | ❌ NOT IMPLEMENTED | No automated reports | — |

---

## Summary Matrix

| Category | Ready | Config | Code | Blocker |
|---|---|---|---|---|
| Environment | 0 | 4 | 2 | 0 |
| Database | 0 | 2 | 2 | 1 |
| Payment | 3 | 4 | 3 | 0 |
| Auth/Session | 3 | 2 | 2 | 0 |
| Queue | 2 | 3 | 0 | 0 |
| Mail | 1 | 2 | 0 | 1 |
| Storage | 4 | 1 | 0 | 0 |
| Logging | 2 | 1 | 0 | 0 |
| Cache | 0 | 3 | 1 | 0 |
| Security | 4 | 1 | 2 | 1 |
| Deploy | 2 | 2 | 0 | 0 |
| Scheduler | 0 | 0 | 1 | 0 |
| **TOTAL** | **21** | **25** | **13** | **3** |

**Verdict:** 3 blockers + 13 code changes required before production.
