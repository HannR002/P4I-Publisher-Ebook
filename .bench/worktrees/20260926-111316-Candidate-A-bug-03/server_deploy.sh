#!/bin/bash
echo "🚀 Memulai Deployment P4I Publisher di Hostinger..."

# 1. Instalasi Dependensi Produksi (Tanpa paket dev)
echo "📦 Menginstal dependensi Composer..."
composer install --optimize-autoloader --no-dev

# 2. Eksekusi Migrasi Basis Data
echo "🗄️ Menjalankan Migrasi Basis Data..."
php artisan migrate --force

# 3. Optimasi Cache Peladen
echo "⚡ Mengoptimasi Cache Laravel..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 4. Instruksi Symlink Manual (Shared Hosting Safe)
echo "🔗 SYMLINK MANUAL DIBUTUHKAN!"
echo "Jalankan perintah ini sesuaikan dengan path domain Anda:"
echo "ln -s /home/uXXXXXXX/domains/domainanda.com/p4i_core/storage/app/public /home/uXXXXXXX/domains/domainanda.com/public_html/storage"

echo "✅ Deployment Skrip Selesai!"
