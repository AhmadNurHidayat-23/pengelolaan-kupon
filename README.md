# Pengelolaan Kupon

Pengelolaan Kupon adalah aplikasi lokal untuk membuat, mengatur, memeriksa, dan menyimulasikan penggunaan kupon. Tujuannya membantu pengelola memahami aturan promosi tanpa sistem pembayaran atau marketplace. Antarmuka berbahasa Indonesia menggunakan gaya Swiss Editorial Minimalist, Helvetica Neue dengan alternatif sistem, serta tepat dua warna utama: cream `#FFF7ED` dan burgundy `#783D56`.

## Fitur utama

- Daftar kupon dengan pencarian kode, filter status, dan pagination.
- Tambah, detail, edit, serta hapus kupon dengan halaman konfirmasi.
- Kode unik yang otomatis disimpan dalam huruf kapital.
- Diskon persentase 1–100 atau nominal rupiah utuh hingga Rp1.000.000.000.
- Tanggal mulai, tanggal berakhir, status aktif, dan batas penggunaan.
- Pemeriksaan kupon tanpa mengubah jumlah penggunaan.
- Simulasi penggunaan dengan transaksi SQLite dan perlindungan batas penggunaan.

Status tersedia sebagai label Aktif, Nonaktif, Belum berlaku, Kedaluwarsa, dan Habis. Tanggal mulai dan berakhir bersifat inklusif berdasarkan zona Asia/Jakarta. Kupon hanya dapat digunakan ketika aktif, sudah memasuki masa berlaku, belum kedaluwarsa, dan masih mempunyai sisa penggunaan.

## Penggunaan

Tekan **Tambah kupon**, isi seluruh aturan, lalu simpan. Klik kode pada daftar untuk melihat detail atau mengubahnya. Batas penggunaan tidak boleh diturunkan melewati jumlah yang sudah terpakai. Jumlah penggunaan tidak dapat diedit secara langsung.

Buka **Validasi & simulasi**, masukkan kode, kemudian tekan **Periksa Kupon**. Jika valid, pilih **Simulasikan penggunaan**. Setiap keberhasilan menambah satu penggunaan; penolakan tidak menambahnya. Ketersediaan diperiksa kembali dalam transaksi saat simulasi diproses agar permintaan bersamaan tidak melampaui kuota.

## Instalasi lokal

Teknologi: Laravel 13, Blade, SQLite, HTML5, CSS3, Vanilla JavaScript, dan Vite. Siapkan PHP 8.3.30+ dengan SQLite, Composer, dan Node.js 22.12+ yang kompatibel dengan Vite 8.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm ci
npm run build
php artisan serve --host=127.0.0.1
```

Pada MAMP, arahkan document root ke folder `public` melalui localhost. Opsional, jalankan `php artisan db:seed` untuk enam kupon contoh ketika database masih kosong. Jalankan `php artisan test` dan `npm run build` untuk pengujian.

Aplikasi tanpa login ini dibatasi ke localhost dan lingkungan lokal. Tambahkan autentikasi serta otorisasi sebelum membuka pengelolaan ke internet. `.env`, credential, dan database tidak disertakan dalam Git.
