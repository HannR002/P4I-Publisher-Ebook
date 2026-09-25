# Rescope Hostinger Deployment Plan

This is a plan only. No deployment has been performed.

## Preconditions

- Product/admin acceptance of the local review build.
- Full encrypted MySQL backup and verified restore procedure.
- Backup of `storage/app`, public covers, and current release files.
- Staging rehearsal using the same PHP/MySQL versions and an anonymized recent data copy.
- Confirm PHP 8.2+, required extensions, write permissions, queue/cron behavior, and available disk.

## Staging rehearsal

1. Put staging in maintenance mode.
2. Upload release to a new versioned directory; do not overwrite the live release in place.
3. Preserve the staging `.env`; set all four rescope flags false.
4. Run `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build` in the build environment if assets are not prebuilt.
5. Run `php artisan migrate --force` (never `migrate:fresh`).
6. Run `php artisan storage:link` only if absent, then `config:cache`, `route:cache`, and `view:cache`.
7. Compare legacy/backfill counts and inspect sample records/files.
8. Run smoke tests: homepage, search/filter, public read/download, registered gate, external OJS redirect, author submission without KYC, manual order/proof, admin verification/grant, analytics, disabled endpoints.

## Production configuration

Preserve the existing production `.env` and add only after backup/review:

```dotenv
MIDTRANS_ENABLED=false
ROYALTY_ENABLED=false
PAYOUT_ENABLED=false
AUTHOR_KYC_ENABLED=false
```

Do not copy `.env.testing`, local keys, database files, logs, or test uploads to production.

## Release sequence

1. Announce maintenance window and stop queue workers.
2. Enable maintenance mode.
3. Take final database/upload backups.
4. Upload/switch to the reviewed release.
5. Run dependency install and additive migrations.
6. Clear then rebuild caches; restart queue workers if used.
7. Run database count checks and the smoke suite.
8. Disable maintenance mode only after the gate passes.
9. Monitor application/error logs, failed jobs, disk, payment submissions, and file responses.

## Rollback

Before user writes: switch back to the previous code release; restore caches. If migrations must be reversed, verify the new tables contain no production data first.

After library/manual-payment/analytics writes: do not blindly migrate down. Switch code forward with a compatibility fix or restore the pre-release database and uploads together after an approved outage. New tables contain user payment proofs/entitlements and their loss is material.

## Go/no-go gate

No-go on any failed migration/count check, missing private file, writable storage failure, route/cache failure, access-policy bypass, exposed proof, disabled legacy endpoint becoming active, or unverified payment granting access.

Current status: **NO-GO / NOT READY FOR DEPLOYMENT** until staging MySQL rehearsal and stakeholder review are complete.
