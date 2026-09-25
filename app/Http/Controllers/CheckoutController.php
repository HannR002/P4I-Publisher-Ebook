<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Book;
use App\Models\BookLicense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderIdGenerator;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function __construct()
    {
        if (! config('features.midtrans')) {
            return;
        }

        // Boot Midtrans config from our config/midtrans.php
        MidtransConfig::$serverKey   = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized  = config('midtrans.is_sanitized');
        MidtransConfig::$is3ds        = config('midtrans.is_3ds');
    }

    /**
     * Process a new checkout request.
     * Accepts: book_ids[] array from the request.
     *
     * BUG-03 FIX: Block purchase if user already owns any selected book.
     * BVA-02 FIX: If gross_amount == 0, bypass Midtrans entirely and
     *             issue BookLicense directly (free/promotional book flow).
     */
    public function store(Request $request)
    {
        abort_unless(config('features.midtrans'), 404, 'Checkout Midtrans tidak aktif. Gunakan pembayaran manual.');

        $request->validate([
            'book_ids'   => 'required|array|min:1',
            'book_ids.*' => 'integer|exists:books,id',
        ]);

        $user  = $request->user();
        $books = Book::whereIn('id', $request->book_ids)
                     ->where('is_published', true)
                     ->get()
                     ->keyBy('id');

        if ($books->isEmpty()) {
            return back()->withErrors(['book_ids' => 'No valid books selected.']);
        }

        // ── BUG-03 FIX: Prevent repurchase ──────────────────────────────────
        $alreadyOwnedIds = BookLicense::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('book_id', $books->keys())
            ->pluck('book_id')
            ->toArray();

        if (!empty($alreadyOwnedIds)) {
            $ownedTitles = $books->only($alreadyOwnedIds)->pluck('title')->join(', ');
            return back()->withErrors([
                'book_ids' => "Anda sudah memiliki lisensi aktif untuk: {$ownedTitles}.",
            ]);
        }
        // ────────────────────────────────────────────────────────────────────

        // Calculate gross_amount from LIVE price at checkout time
        $grossAmount = $books->sum('price');
        $orderId     = OrderIdGenerator::generate();

        // ── BVA-02: FREE BOOK BYPASS (grossAmount == 0) ──────────────────────
        // If total is Rp 0, bypass Midtrans entirely.
        // Create order as 'success' and issue licenses immediately.
        if ((float) $grossAmount === 0.0) {
            DB::beginTransaction();
            try {
                $order = Order::create([
                    'id'           => $orderId,
                    'user_id'      => $user->id,
                    'gross_amount' => 0,
                    'status'       => 'success',
                    'payment_type' => 'free',
                ]);

                foreach ($books as $book) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'book_id'  => $book->id,
                        'price'    => 0,
                    ]);

                    BookLicense::firstOrCreate(
                        ['user_id' => $user->id, 'book_id' => $book->id],
                        [
                            'order_id'    => $order->id,
                            'license_key' => (string) Str::uuid(),
                            'status'      => 'active',
                        ]
                    );
                }

                DB::commit();

                event(new \App\Events\OrderPaidEvent($order));

                return redirect()->route('my-library')
                    ->with('success', 'Buku gratis berhasil ditambahkan ke perpustakaan Anda!');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['checkout' => 'Gagal memproses buku gratis: ' . $e->getMessage()]);
            }
        }
        // ────────────────────────────────────────────────────────────────────

        // ── PAID BOOK FLOW (grossAmount > 0): call Midtrans ─────────────────
        DB::beginTransaction();
        try {
            // Create Order
            $order = Order::create([
                'id'           => $orderId,
                'user_id'      => $user->id,
                'gross_amount' => $grossAmount,
                'status'       => 'pending',
            ]);

            // Create OrderItems — lock price historically at this moment
            foreach ($books as $book) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id'  => $book->id,
                    'price'    => $book->price,
                ]);
            }

            // Build Midtrans item_details
            $itemDetails = $books->map(fn($book) => [
                'id'       => (string) $book->id,
                'price'    => (int) $book->price,
                'quantity' => 1,
                'name'     => $book->title,
            ])->values()->toArray();

            // Build Midtrans payload
            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => (int) $grossAmount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email'      => $user->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Persist snap_token to allow page reload without re-calling Midtrans API
            $order->snap_token = $snapToken;
            $order->save();

            DB::commit();

            return redirect()->route('checkout.show', $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['checkout' => 'Checkout failed: ' . $e->getMessage()]);
        }
        // ────────────────────────────────────────────────────────────────────
    }

    /**
     * Show the payment page for an existing pending order.
     * TC-07 FIX: If order is already 'success', redirect to library instead of
     * showing a stale payment page with a clickable "Bayar" button.
     */
    public function show(Request $request, $orderId)
    {
        abort_unless(config('features.midtrans'), 404, 'Checkout Midtrans tidak aktif.');

        $order = Order::with('items.book')
                      ->where('id', $orderId)
                      ->where('user_id', $request->user()->id)
                      ->firstOrFail();

        // TC-07 FIX: Redirect away from payment page if order already settled.
        if ($order->status === 'success') {
            return redirect()->route('my-library')
                ->with('info', 'Pembayaran untuk pesanan ini sudah berhasil. Buku tersedia di perpustakaan Anda.');
        }

        if (in_array($order->status, ['failed', 'expired'])) {
            return redirect()->route('my-orders')
                ->with('warning', 'Pesanan ini sudah ' . ($order->status === 'failed' ? 'gagal' : 'kadaluarsa') . '.');
        }

        // TC-10 FIX: Auto-renew Snap Token if it's older than 50 minutes and status is pending
        if ($order->status === 'pending' && $order->updated_at->diffInMinutes(now()) >= 50) {
            try {
                $itemDetails = $order->items->map(fn($item) => [
                    'id'       => (string) $item->book_id,
                    'price'    => (int) $item->price,
                    'quantity' => 1,
                    'name'     => $item->book->title,
                ])->values()->toArray();

                $params = [
                    'transaction_details' => [
                        'order_id'     => $order->id,
                        'gross_amount' => (int) $order->gross_amount,
                    ],
                    'item_details' => $itemDetails,
                    'customer_details' => [
                        'first_name' => $request->user()->name,
                        'email'      => $request->user()->email,
                    ],
                ];

                $snapToken = Snap::getSnapToken($params);
                $order->snap_token = $snapToken;
                // updated_at is automatically updated on save()
                $order->save();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[Checkout] Failed to renew snap token', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                return redirect()->route('my-orders')->with('warning', 'Gagal memperbarui sesi pembayaran. Silakan coba lagi nanti.');
            }
        }

        return view('checkout.show', compact('order'));
    }

    /**
     * Success landing page (UX only — real status comes from webhook).
     */
    public function success(Request $request)
    {
        $order = null;
        if ($request->has('order_id')) {
            $order = Order::where('id', $request->order_id)
                          ->where('user_id', $request->user()->id)
                          ->first();
        }
        return view('checkout.success', compact('order'));
    }

    /**
     * Pending landing page.
     */
    public function pending(Request $request)
    {
        return view('checkout.pending');
    }
}
