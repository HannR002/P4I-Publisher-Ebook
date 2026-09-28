<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - {{ $order->id }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0f1117;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
        }
        .card {
            background: #1a1d2e;
            border: 1px solid #2d3147;
            border-radius: 16px;
            padding: 40px;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; color: #fff; }
        .order-id { font-size: 0.8rem; color: #718096; margin-bottom: 28px; }
        .divider { border-color: #2d3147; margin: 20px 0; }
        .item-row { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
        .item-title { font-size: 0.95rem; color: #cbd5e0; }
        .item-price { font-size: 0.95rem; font-weight: 600; color: #a3bffa; white-space: nowrap; }
        .total-row { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #2d3147; }
        .total-label { font-size: 1rem; font-weight: 700; color: #fff; }
        .total-amount { font-size: 1.25rem; font-weight: 800; color: #68d391; }
        .notice {
            background: #1a2d23;
            border: 1px solid #276749;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.8rem;
            color: #9ae6b4;
            margin: 20px 0;
            line-height: 1.6;
        }
        .btn-pay {
            display: block;
            width: 100%;
            padding: 16px;
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            letter-spacing: 0.5px;
        }
        .btn-pay:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-pay:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    </style>
    {{-- Snap.js: Sandbox or Production based on config --}}
    @if(config('features.midtrans') && config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @elseif(config('features.midtrans'))
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif
</head>
<body>
    <div class="card">
        <h1>Ringkasan Pembelian</h1>
        <p class="order-id">Order ID: {{ $order->id }}</p>

        @foreach($order->items as $item)
            <div class="item-row">
                <span class="item-title">{{ $item->book->title ?? 'Buku' }}</span>
                <span class="item-price">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
            </div>
        @endforeach

        <hr class="divider">

        <div class="total-row">
            <span class="total-label">Total</span>
            <span class="total-amount">Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</span>
        </div>

        <div class="notice">
            ⚠️ Status pembayaran <strong>tidak diperbarui dari popup ini</strong>.
            Konfirmasi final hanya dari server Midtrans via webhook.
        </div>

        @if($errors->any())
            <div style="background:#2d1515;border:1px solid #c53030;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#fc8181;font-size:0.85rem;">
                {{ $errors->first() }}
            </div>
        @endif

        @if(config('features.midtrans'))
        <button id="pay-btn" class="btn-pay" onclick="startPayment()">
            Bayar Sekarang
        </button>
        @endif
    </div>

    <script>
        const SNAP_TOKEN = "{{ $order->snap_token }}";

        function startPayment() {
            const btn = document.getElementById('pay-btn');
            btn.disabled = true;
            btn.textContent = 'Memuat gateway...';

            snap.pay(SNAP_TOKEN, {
                onSuccess: function(result) {
                    // UX only — do NOT trust this for DB status update
                    window.location.href = "{{ route('checkout.success') }}?order_id={{ $order->id }}";
                },
                onPending: function(result) {
                    window.location.href = "{{ route('checkout.pending') }}";
                },
                onError: function(result) {
                    btn.disabled = false;
                    btn.textContent = 'Bayar Sekarang';
                    alert('Terjadi kesalahan saat proses pembayaran. Silakan coba lagi.');
                },
                onClose: function() {
                    btn.disabled = false;
                    btn.textContent = 'Bayar Sekarang';
                }
            });
        }
    </script>
</body>
</html>
