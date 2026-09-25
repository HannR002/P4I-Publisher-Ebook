<?php
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BookLicense;

// Hapus buku selain ID 1 agar constraint tidak error
Book::where('id', '!=', 1)->delete();

// Update Buku ID 1 (yang sudah dimiliki user test)
$book1 = Book::find(1);
if ($book1) {
    $book1->update([
        'title' => 'Di Bawah Langit Jakarta',
        'author' => 'Pramoedya L.',
        'description' => 'Sebuah novel fiksi sejarah yang menceritakan kehidupan masyarakat urban Jakarta di era transisi pasca kemerdekaan, dengan segala intrik dan romantikanya.',
        'price' => 85000,
        'cover_image_path' => 'https://placehold.co/400x600/e2e8f0/1e293b?text=Di+Bawah\nLangit+Jakarta',
    ]);
}

$books = [
    [
        'title' => 'Seni Berpikir Tenang',
        'author' => 'Desi Anwar',
        'description' => 'Buku self-improvement yang memandu Anda menemukan ketenangan batin di tengah hiruk pikuk dunia modern yang serba cepat.',
        'price' => 75000,
        'cover_image_path' => 'https://placehold.co/400x600/e2e8f0/1e293b?text=Seni+Berpikir\nTenang',
        'file_path' => 'private_books/dummy-ebook.pdf',
        'is_published' => true,
    ],
    [
        'title' => 'Jejak Langkah Kopi Nusantara',
        'author' => 'Raditya D.',
        'description' => 'Eksplorasi budaya dan sejarah panjang kopi di Indonesia, dari perkebunan kolonial hingga menjamurnya kedai kopi kekinian.',
        'price' => 120000,
        'cover_image_path' => 'https://placehold.co/400x600/e2e8f0/1e293b?text=Jejak+Langkah\nKopi',
        'file_path' => 'private_books/dummy-ebook.pdf',
        'is_published' => true,
    ],
    [
        'title' => 'Memahami Alam Semesta',
        'author' => 'Prof. Yohanes S.',
        'description' => 'Penjelasan sains populer tentang kosmologi, lubang hitam, dan asal muasal terbentuknya galaksi kita dalam bahasa yang mudah dipahami.',
        'price' => 150000,
        'cover_image_path' => 'https://placehold.co/400x600/e2e8f0/1e293b?text=Memahami\nAlam+Semesta',
        'file_path' => 'private_books/dummy-ebook.pdf',
        'is_published' => true,
    ],
    [
        'title' => 'Kiat Finansial Milenial',
        'author' => 'Prita Hapsari',
        'description' => 'Panduan praktis mengelola keuangan, investasi reksa dana, dan mempersiapkan dana pensiun sejak usia dua puluhan.',
        'price' => 65000,
        'cover_image_path' => 'https://placehold.co/400x600/e2e8f0/1e293b?text=Kiat\nFinansial',
        'file_path' => 'private_books/dummy-ebook.pdf',
        'is_published' => true,
    ],
    [
        'title' => 'Antologi Puisi Senja',
        'author' => 'Sapardi D.',
        'description' => 'Kumpulan puisi liris yang merangkum keindahan alam, kerinduan, dan refleksi kehidupan manusia dari sudut pandang seorang penyair.',
        'price' => 45000,
        'cover_image_path' => 'https://placehold.co/400x600/e2e8f0/1e293b?text=Antologi\nPuisi+Senja',
        'file_path' => 'private_books/dummy-ebook.pdf',
        'is_published' => true,
    ]
];

foreach ($books as $bookData) {
    Book::create($bookData);
}

echo "Berhasil update buku utama dan membuat 5 buku realistis tambahan!\n";
