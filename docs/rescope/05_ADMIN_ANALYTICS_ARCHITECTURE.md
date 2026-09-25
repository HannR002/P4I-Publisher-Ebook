# Admin Analytics Architecture

## Event model

`analytics_events` records a bounded event name, optional library item/user, pseudonymous guest ID, search term/result count, allow-listed metadata, and occurrence time.

Supported events: `detail_view`, `read_start`, `read_progress`, `download`, `search`, `search_result_click`, `external_open`, `payment_started`, and `payment_submitted`.

Raw IP addresses are not stored. Guest identity is a SHA-256 digest of the session ID. Metadata accepts only known filter keys; uncontrolled request payloads are discarded.

## MVP dashboard

- Inventory totals, publication states, new 7/30 day items, free/paid, and count by type.
- Reads today/7/30 days and downloads over 30 days.
- Most read, trending, and rarely read.
- Top searches and zero-result searches.
- Verified, pending, and rejected manual-order values.

Rarely read means published for at least `library.rarely_read_after_days` (default 30) with fewer than `library.rarely_read_threshold` reads (default 3). Newly published content is excluded.

Trending ranks recent 7-day engagement minus the preceding 7-day engagement, not lifetime totals. Tests use fixed periods/counts to keep the result deterministic.

## Scale plan

The append-only table is appropriate for MVP traffic. Add retention and a daily aggregate table before sustained high volume. Aggregate by date, event, item, and selected bounded dimensions, then delete detailed anonymous events according to an approved retention policy. Do not add raw IP tracking.

Deferred metrics include filter-frequency reporting UI, click-through ratios, publishing processing time, stock/fulfilment, missing-file/cover operations, URL health, failed imports/queues, and storage usage.
