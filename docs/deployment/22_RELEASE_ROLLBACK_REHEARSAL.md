# Release Rollback Rehearsal

## Rollback Plan Overview
Attempting to roll back production using `php artisan migrate:rollback` is **NOT SUPPORTED** and **FAILED** during rehearsal due to MySQL's strict foreign key index locking behavior. 

The proven and required rollback strategy is a **Pre-Migration Snapshot Restore**.

## Application Rollback Strategy
If a deployment fails, the application must be rolled back by reverting both the database state and the application code files to the $N-1$ release baseline.

**Steps to execute if rollback is required:**
1. Switch the application to maintenance mode (`php artisan down`).
2. Drop and recreate the target database, or drop all tables.
3. Import the pre-migration SQL backup snapshot created immediately prior to deployment.
4. Restore the application files to the $N-1$ baseline (e.g., git checkout previous commit, or restore directory backup).
5. Run `php artisan optimize:clear` and `php artisan config:cache`.
6. Disable maintenance mode (`php artisan up`).

## Warning: Post-Deploy Write Loss
A pre-migration DB restore **will irreparably discard any legitimate writes** (e.g., new user registrations, payments, read analytics) made to the database *after* the deployment window was opened.

**Mitigation:** 
- Deployment must be executed within a strict, scheduled maintenance window where external writes are halted.
- The verification (smoke tests, visual review) must happen as quickly as possible while the app remains in maintenance mode or a read-only mode to prevent new transactions before the release is officially accepted.

## Snapshot Restore Rehearsal Result
- **Status:** PASS
- **Details:** 
  1. A pre-migration snapshot (`p4i_rehearsal_snapshot.sql`) was created from the `p4i_rehearsal` database.
  2. The `php artisan migrate --force` command was executed to mutate the schema.
  3. The `p4i_rehearsal` database was dropped and recreated.
  4. The snapshot was successfully imported.
  5. Baseline counts (Users: 6, Authors: 2, Books: 4, Book Submissions: 3) were correctly restored, confirming zero data or schema loss from the rollback process.
