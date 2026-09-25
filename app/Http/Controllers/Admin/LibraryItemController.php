<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LibraryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryItemController extends Controller
{
    public function index(Request $request)
    {
        $items = LibraryItem::with('creators')->when($request->type, fn ($q, $type) => $q->where('type', $type))->latest()->paginate(20)->withQueryString();
        return view('admin.library.index', compact('items'));
    }

    public function create() { return view('admin.library.form', ['item' => new LibraryItem(), 'categories' => Category::orderBy('name')->get()]); }

    public function store(Request $request)
    {
        $item = LibraryItem::create($this->validated($request));
        $this->syncRelations($request, $item);
        return redirect()->route('admin.library.index')->with('success', 'Koleksi berhasil ditambahkan.');
    }

    public function edit(LibraryItem $libraryItem) { return view('admin.library.form', ['item' => $libraryItem->load(['creators', 'categories', 'files']), 'categories' => Category::orderBy('name')->get()]); }

    public function update(Request $request, LibraryItem $libraryItem)
    {
        abort_if($libraryItem->source_type === 'legacy_book', 422, 'Ubah metadata buku lama melalui halaman Kelola Buku agar proyeksi tetap konsisten.');
        $libraryItem->update($this->validated($request, $libraryItem));
        $this->syncRelations($request, $libraryItem);
        return redirect()->route('admin.library.index')->with('success', 'Koleksi berhasil diperbarui.');
    }

    private function validated(Request $request, ?LibraryItem $item = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(config('library.types'))], 'title' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255', Rule::unique('library_items')->ignore($item?->id)],
            'description' => ['nullable', 'string'], 'synopsis' => ['nullable', 'string'], 'abstract' => ['nullable', 'string'],
            'featured_excerpt' => ['nullable', 'string', 'max:2000'], 'excerpt_source' => ['nullable', 'max:255'], 'excerpt_page' => ['nullable', 'max:50'],
            'publisher' => ['nullable', 'max:255'], 'publication_date' => ['nullable', 'date'], 'publication_year' => ['nullable', 'integer', 'between:1000,2100'],
            'isbn' => ['nullable', 'max:32'], 'issn' => ['nullable', 'max:32'], 'doi' => ['nullable', 'max:255'], 'language' => ['nullable', 'max:20'],
            'keywords' => ['nullable', 'string'], 'source_type' => ['required', 'max:30'], 'source_url' => ['nullable', 'url:http,https'],
            'access_policy' => ['required', Rule::in(config('library.access_policies'))], 'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])], 'published_at' => ['nullable', 'date'],
            'category_ids' => ['nullable', 'array'], 'category_ids.*' => ['integer', 'exists:categories,id'],
            'creators' => ['nullable', 'string', 'max:2000'],
            'publication_file' => ['nullable', 'file', 'mimes:pdf,epub', 'max:51200'],
            'external_file_url' => ['nullable', 'url:http,https'], 'download_allowed' => ['nullable', 'boolean'],
        ]);
    }

    private function syncRelations(Request $request, LibraryItem $item): void
    {
        $item->categories()->sync($request->input('category_ids', []));
        $names = collect(explode(',', (string) $request->input('creators')))->map->trim()->filter()->values();
        $item->creators()->delete();
        foreach ($names as $position => $name) $item->creators()->create(['name' => $name, 'role' => 'author', 'sort_order' => $position]);

        if ($request->hasFile('publication_file')) {
            $path = $request->file('publication_file')->store('library-items', 'local');
            $item->files()->updateOrCreate(['is_primary' => true], [
                'label' => 'Berkas utama', 'file_type' => 'publication',
                'mime_type' => $request->file('publication_file')->getMimeType(), 'file_path' => $path,
                'external_url' => null, 'visibility' => 'private',
                'download_allowed' => $request->boolean('download_allowed'),
                'file_size' => $request->file('publication_file')->getSize(),
            ]);
        } elseif ($request->filled('external_file_url')) {
            $item->files()->updateOrCreate(['is_primary' => true], [
                'label' => 'Sumber eksternal', 'file_type' => 'external', 'file_path' => null,
                'external_url' => $request->external_file_url, 'visibility' => 'external',
                'download_allowed' => $request->boolean('download_allowed'),
            ]);
        }
    }
}
