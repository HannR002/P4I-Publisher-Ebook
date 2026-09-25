<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

        $baseQuery = Order::where('status', 'success')
            ->whereBetween('created_at', [$start, $end]);

        $totalRevenue = (clone $baseQuery)->sum('gross_amount');
        $totalTransactions = (clone $baseQuery)->count();
        $averageOrderValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $totalBooksSold = OrderItem::whereHas('order', function ($query) use ($start, $end) {
            $query->where('status', 'success')
                  ->whereBetween('created_at', [$start, $end]);
        })->count();

        $topBooks = OrderItem::select('book_id', \DB::raw('COUNT(*) as total_sold'), \DB::raw('SUM(price) as total_revenue'))
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->where('status', 'success')
                      ->whereBetween('created_at', [$start, $end]);
            })
            ->with('book')
            ->groupBy('book_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $recentOrders = Order::with('user')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.reports.index', compact(
            'startDate', 'endDate', 'totalRevenue', 'totalTransactions',
            'averageOrderValue', 'totalBooksSold', 'topBooks', 'recentOrders'
        ));
    }

    public function exportOrders(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $status = $request->input('status', 'all');

        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

        $query = Order::with(['user', 'orderItems'])->whereBetween('created_at', [$start, $end]);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-transaksi-' . now()->format('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        return new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order ID', 'Tanggal', 'Waktu', 'Nama Pembeli', 'Email', 'Total Item', 'Metode Pembayaran', 'Status', 'Total Tagihan (IDR)']);

            foreach ($query->lazy(500) as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->created_at->format('Y-m-d'),
                    $order->created_at->format('H:i:s'),
                    $order->user->name ?? 'Unknown',
                    $order->user->email ?? 'Unknown',
                    $order->orderItems->count(),
                    $order->payment_type,
                    $order->status,
                    $order->gross_amount
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportBooks(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-performa-buku-' . now()->format('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        return new StreamedResponse(function () use ($start, $end) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID Buku', 'Judul Buku', 'Penulis', 'Harga Saat Ini', 'Total Eksemplar Terjual', 'Total Pendapatan (IDR)']);

            $booksData = OrderItem::select('book_id', \DB::raw('COUNT(*) as total_sold'), \DB::raw('SUM(price) as total_revenue'))
                ->whereHas('order', function ($q) use ($start, $end) {
                    $q->where('status', 'success')
                      ->whereBetween('created_at', [$start, $end]);
                })
                ->with('book')
                ->groupBy('book_id')
                ->cursor();

            foreach ($booksData as $data) {
                fputcsv($handle, [
                    $data->book_id,
                    $data->book->title ?? 'Deleted Book',
                    $data->book->author ?? 'Unknown',
                    $data->book->price ?? 0,
                    $data->total_sold,
                    $data->total_revenue
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
