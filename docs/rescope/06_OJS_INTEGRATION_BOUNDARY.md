# OJS Integration Boundary

OJS remains authoritative for journal submission, peer review, editorial decisions, issues, and publication. P4I Digital Library is a discovery/access layer only.

No direct OJS database connection is introduced. A future importer may use the OJS REST API or a controlled metadata export.

## Future import contract

An imported record maps to `LibraryItem` with:

- `source_type=ojs`;
- `source_url` as the canonical OJS landing page;
- type `journal`, `journal_issue`, or `journal_article`;
- hierarchical links using `parent_id` (e.g. article belongs to issue, issue belongs to journal);
- DOI, ISSN, title, abstract, publisher/journal, publication date/year, language;
- ordered creators and roles;
- categories/keywords mapped through an explicit vocabulary;
- `access_policy=external` unless P4I is authorized to mirror a file;
- stable external identifier inside bounded `metadata` for idempotent upsert.

The importer must never copy long copyrighted passages automatically. Abstract/metadata may be imported according to source permissions; `featured_excerpt` remains a short, admin-controlled field.

## Operational requirements before implementation

- Confirm OJS version/API authentication, rate limits, and pagination.
- Define stable journal/article identifiers and deletion/unpublish behavior.
- Log import failures without secrets and make retries idempotent.
- Validate external URLs and expose broken-link reporting.
- Never update OJS editorial state from this application.
