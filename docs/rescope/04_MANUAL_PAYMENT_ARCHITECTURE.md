# Manual Payment Architecture

## Tables

- `payment_methods`: admin-managed bank/e-wallet/QRIS/other method, account display data, official QR asset, instructions, active state, order.
- `manual_orders`: user, type, totals, notes, and state.
- `manual_order_items`: polymorphic-by-name item snapshot with description, quantity, unit price, subtotal.
- `payment_submissions`: method, amount, private proof path, verification state/audit fields.
- `library_access_grants`: paid digital entitlement created only after verification.

Expected order states are `awaiting_payment`, `payment_submitted`, `verified`, `rejected`, `processing`, `completed`, and `cancelled`.

## Flow

1. An authenticated user orders an item whose policy is `manual_purchase` or `physical_only`.
2. Server snapshots the item description and price in a manual order.
3. Only active admin-managed methods are displayed. Account numbers are never hardcoded in Blade.
4. Official uploaded QR images may be shown. Without one, the UI displays the account/phone number and “Salin Nomor”; no fake QR is generated.
5. Proof is validated (JPEG/PNG/PDF, max 5 MB) and stored on the private local disk.
6. Admin views the protected proof and either verifies or rejects it.
7. Verification locks the submission/order. Digital orders receive an idempotent `library_access_grants` row; physical orders move to `processing`.

The current MVP handles one library item per order and zero shipping cost. Cart consolidation, addresses, courier/shipping prices, publishing-service order items, stock reservation, and fulfilment transitions remain follow-up work.
