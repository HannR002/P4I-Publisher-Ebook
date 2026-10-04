# 09 Access Policies

## Overview
This document outlines the enforced access policies for digital library items. Access enforcement is managed primarily by `LibraryAccessService`, which determines reading and downloading privileges based on user authentication, manual orders, and the item's policy constraints.

## Enforced Policies

1. **`public_read_download`**
   - **Read**: Available to all (Guests & Authenticated).
   - **Download**: Available to all (Guests & Authenticated).
   - **Purchase/Order**: Disabled.

2. **`public_read_only`**
   - **Read**: Available to all (Guests & Authenticated).
   - **Download**: Forbidden.
   - **Purchase/Order**: Disabled.

3. **`registered_read_download`**
   - **Read**: Authenticated users only.
   - **Download**: Authenticated users only.
   - **Purchase/Order**: Disabled.

4. **`registered_read_only`**
   - **Read**: Authenticated users only.
   - **Download**: Forbidden.
   - **Purchase/Order**: Disabled.

5. **`manual_purchase` (Paid Digital Publication)**
   - **Read**: Authenticated users holding an active `LibraryAccessGrant` for the item.
   - **Download**: Authenticated users holding an active `LibraryAccessGrant`.
   - **Purchase/Order**: Available for authenticated users who do not yet possess an access grant.

6. **`physical_only` (Printed Book)**
   - **Read**: Forbidden (handled outside the platform).
   - **Download**: Forbidden.
   - **Purchase/Order**: Available for authenticated users to order a physical copy.

7. **`external` (OJS/Third-Party)**
   - **Read**: Redirects to the external source URL.
   - **Download**: Forbidden (handled externally).
   - **Purchase/Order**: Disabled.
   - **Security**: Strict enforcement of URL schemes (`http`, `https`), rejecting `javascript:`, `file:`, etc.

## Storage Security
- All uploaded digital manuscripts and payment proofs are stored in `local` (private) disks outside the public `storage/app/public` web root.
- Access via URL routing uses server-side stream/download functions (e.g. `Storage::disk('local')->download()`) preventing direct link scraping.
- `download_allowed` flag on individual files supersedes policy overrides if explicitly restricted.
