<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookUploadController extends Controller
{
    /**
     * Display a list of all books for admin management.
     */
    public function index()
    {
        $books = Book::withCount([
                'licenses as active_licenses_count' => fn ($q) => $q->where('status', 'active')
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Simple Analytics
        $totalRevenue = \App\Models\Order::where('status', 'success')->sum('gross_amount');
        $successfulOrders = \App\Models\Order::where('status', 'success')->count();
        $totalBooks = Book::count();

        // Monthly Sales Data (for Chart.js)
        $currentYear = date('Y');
        $monthlySales = \App\Models\Order::selectRaw('MONTH(created_at) as month, SUM(gross_amount) as total')
            ->where('status', 'success')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')->toArray();

        // Fill empty months with 0
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlySales[$i] ?? 0;
        }

        return view('admin.books.index', compact('books', 'totalRevenue', 'successfulOrders', 'totalBooks', 'chartData'));
    }

    /**
     * Show the upload form.
     */
    public function create()
    {
        return view('admin.books.upload');
    }

    /**
     * Store a new book (PDF + optional cover, stored on local disk).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'author'       => 'required|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'price'        => 'required|numeric|min:0',
            'pdf_file'     => 'required|file|mimes:pdf|max:51200', // max 50MB
            'cover_image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_published' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'categories'   => 'nullable|string',
        ]);

        $pdfFile = $request->file('pdf_file');
        
        // Tahap 5: Magic Bytes Validation (Security)
        $handle = fopen($pdfFile->getPathname(), 'r');
        $magicBytes = fread($handle, 4);
        fclose($handle);
        
        if ($magicBytes !== "%PDF") {
            return back()->withErrors(['pdf_file' => 'File yang diunggah bukan PDF yang valid (Magic bytes mismatch).'])->withInput();
        }

        // Store PDF privately
        $pdfPath = $pdfFile->store('private_books', 'local');

        // Store cover image publicly (if provided)
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
            $coverPath = Storage::disk('public')->url($coverPath);
        }

        $book = Book::create([
            'title'            => $request->title,
            'author'           => $request->author,
            'description'      => $request->description,
            'price'            => $request->price,
            'cover_image_path' => $coverPath,
            'file_path'        => $pdfPath,
            'is_published'     => $request->boolean('is_published'),
        ]);

        // Simpan relasi kategori
        if ($request->filled('categories')) {
            $categoryNames = array_map('trim', explode(',', $request->categories));
            $categoryIds = [];
            foreach ($categoryNames as $name) {
                if (!empty($name)) {
                    $slug = Str::slug($name);
                    $category = \App\Models\Category::firstOrCreate(
                        ['slug' => $slug],
                        ['name' => $name]
                    );
                    $categoryIds[] = $category->id;
                }
            }
            $book->categories()->sync($categoryIds);
        }

        app(\App\Services\LibraryItemSynchronizer::class)->sync($book);

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku berhasil diunggah!');
    }

    /**
     * Toggle the published status of a book.
     */
    public function togglePublish(Book $book)
    {
        $book->is_published = !$book->is_published;
        $book->save();

        $status = $book->is_published ? 'dipublikasikan' : 'disembunyikan dari katalog';
        return back()->with('success', "Buku \"{$book->title}\" berhasil {$status}.");
    }
}
