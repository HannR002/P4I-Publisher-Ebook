<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\BookLicense;

class LibraryController extends Controller
{
    /**
     * "Perpustakaan Saya" — buku-buku yang dimiliki user (lisensi aktif).
     */
    public function index()
    {
        $licenses = BookLicense::with('book')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('my-library', compact('licenses'));
    }

    /**
     * "Riwayat Transaksi" — semua pesanan user, terbaru duluan.
     */
    public function orders()
    {
        $orders = Order::with('items.book')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('my-orders', compact('orders'));
    }
}
