# PHASE 3B DEVELOPMENT FIXTURES

## Overview
This document describes `DevelopmentVisualFixtureSeeder` which exists solely to populate a local development database with safe synthetic states for Phase 3B visual/render verification (Book, Journal, Issue, Article, Reader, and Manual Payment checkout). 

## 1. Production Guard
The seeder runs strict environment checks:
```php
if (!app()->environment('local')) {
    $this->command->error('This seeder can only be run in local environment!');
    return;
}
```
This ensures dummy data is never populated in staging or production.

## 2. Records Generated
The seeder uses idempotent generation via `updateOrCreate` and `firstOrCreate` strategies.

- **Author User:** `fixture_test@p4i.test` (password: `password123`)
- **Journal Hierarchy:**
  - `jurnal-contoh-p4i-fixture` (Journal)
  - `jurnal-contoh-p4i-vol-1-no-1` (Issue)
  - `artikel-contoh-pengujian-tampilan` (Article)
- **Books:**
  - `buku-publik-test` (Public Read & Download)
  - `buku-premium-test` (Manual Purchase / Digital Edition)
  - `buku-fisik-test` (Physical Only Edition)
- **Reader / PDF Fixture:**
  - `fixtures/test_reader.pdf` (Mock PDF file created inside private local storage)
  - `Legacy Book for DRM Test` with `BookLicense` attached to fixture user to test digital reader layout.
- **Manual Payment UX States:**
  - A synthetic payment method (`Bank Test P4I`).
  - 5 explicit orders modeling each manual payment state: `pending`, `payment_submitted`, `verified`, `processing`, and `rejected`.

## 3. Synthetic Payment Methods
No real banking info is included. `Bank Test P4I` uses account number `0000000000` with explicit testing instructions on-screen.

## 4. How to Run Locally
Run this seeder standalone to populate your local database:
```bash
php artisan db:seed --class=DevelopmentVisualFixtureSeeder
```

## 5. How to Reset / Remove
Currently, the easiest way to remove fixture data is via migrate fresh (wiping all local data) because this seeder is strictly for local dev.
```bash
php artisan migrate:fresh --seed
```
If you wish to remove *only* fixture data, you must manually delete the users and library items referencing 'fixture' slugs or 'fixture_test@p4i.test' emails.

## 6. Idempotency Behavior
Repeated runs of `php artisan db:seed --class=DevelopmentVisualFixtureSeeder` will NOT duplicate most entities (e.g., Journals, Books) due to the use of `updateOrCreate` on unique slugs/emails. Orders and payment submissions have similarly been designed to update matching records by `user_id` and `status` to prevent bloat.
