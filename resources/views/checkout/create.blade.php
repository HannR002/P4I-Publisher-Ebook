<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — {{ $book->title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
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
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        }
        .badge {
            display: inline-block;
            background: #2d3748;
            color: #a0aec0;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
        h1 { font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 6px; }
        .author { color: #718096; font-size: 0.9rem; margin-bottom: 24px; }
        .desc { color: #a0aec0; font-size: 0.9rem; line-height: 1.7; margin-bottom: 28px; }
        .price-row {
            display: flex; justify-content: space-between; align-items: center;
            background: #12141f; border: 1px solid #2d3147; border-radius: 10px;
            padding: 16px 20px; margin-bottom: 24px;
        }
        .price-label { color: #718096; font-size: 0.85rem; }
        .price-amount { font-size: 1.4rem; font-weight: 800; color: #68d391; }
        .user-info {
            background: #1a2d23; border: 1px solid #276749; border-radius: 8px;
            padding: 10px 14px; font-size: 0.8rem; color: #9ae6b4; margin-bottom: 20px;
        }
        @if(session()->has('errors'))
        .error-box {
            background: #2d1515; border: 1px solid #c53030; border-radius: 8px;
            padding: 12px 16px; color: #fc8181; font-size: 0.85rem; margin-bottom: 16px;
        }
        @endif
        .btn-checkout {
            display: block; width: 100%; padding: 16px;
            font-size: 1.05rem; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; border-radius: 10px; cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            letter-spacing: 0.5px;
        }
        .btn-checkout:hover { opacity: 0.9; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">📚 P4I Digital Library</div>
        <h1>{{ $book->title }}</h1>
        <p class="author">oleh {{ $book->author }}</p>

        @if($book->description)
        <p class="desc">{{ $book->description }}</p>
        @endif

        <div class="price-row">
            <span class="price-label">Harga</span>
            <span class="price-amount">Rp {{ number_format($book->price, 0, ',', '.') }}</span>
        </div>

        <div class="user-info">
            ✅ Login sebagai: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
        </div>

        @if($errors->any())
        <div class="error-box">
            ⚠️ {{ $errors->first() }}
        </div>
        @endif

        {{-- POST ke /checkout dengan book_ids[] —  satu klik langsung ke Snap --}}
        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            <input type="hidden" name="book_ids[]" value="{{ $book->id }}">
            <button type="submit" class="btn-checkout">
                🛒 Beli Sekarang — Rp {{ number_format($book->price, 0, ',', '.') }}
            </button>
        </form>
    </div>
</body>
</html>
