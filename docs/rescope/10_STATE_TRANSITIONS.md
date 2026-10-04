# 10 State Transitions

## Manual Order Subsystem

The manual payment processing follows a strict state machine to prevent replay attacks and race conditions. Financial-grade state validation is enforced using database transaction locks (`lockForUpdate`).

### Order Status Flow (`ManualOrder`)

1. **`awaiting_payment`**
   - **Trigger**: User creates an order for a `manual_purchase` or `physical_only` item.
   - **Allowed Actions**: User can upload payment proof (`PaymentSubmission`).
   - **Transitions To**: `payment_submitted`.

2. **`payment_submitted`**
   - **Trigger**: User successfully uploads proof of payment.
   - **Transitions To**: 
     - `verified` (for digital publications).
     - `processing` (for physical books).
     - `rejected`.

3. **`verified` / `processing`**
   - **Trigger**: Admin approves the `PaymentSubmission`. Digital items automatically generate a `LibraryAccessGrant`.
   - **Note**: Terminal state for digital goods. Physical goods may have further shipping transitions externally.

4. **`rejected`**
   - **Trigger**: Admin rejects the `PaymentSubmission` with a required reason.
   - **Allowed Actions**: Order is marked rejected, but the user is permitted to submit a new payment proof which loops the order back to `payment_submitted`.

### Submission Status Flow (`PaymentSubmission`)

1. **`submitted`**
   - **Trigger**: Initial upload of proof.
   - **Transitions To**: `verified` or `rejected`.

2. **`verified`**
   - **Trigger**: Admin approval.
   - **Idempotency**: Strictly idempotent. If the submission is already verified, the verification controller gracefully exits without generating duplicate access grants or altering the modified timestamp.

3. **`rejected`**
   - **Trigger**: Admin rejection.
   - **Idempotency**: Strictly idempotent. If already rejected, the operation safely exits.

### Validation Checks
- **Amount Integrity**: During proof submission, the backend strictly verifies that the user-provided `amount` precisely matches the calculated `ManualOrder` subtotal/total. Discrepancies are rejected before storage.
- **Race Condition Prevention**: Database row locking (`DB::transaction` with `lockForUpdate`) ensures concurrent verification/rejection requests do not corrupt state.
