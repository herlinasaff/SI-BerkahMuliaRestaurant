<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Aplikasi ini menggunakan pola arsitektur modern standar industri yang disebut Decoupled Architecture (Terpisah Antara Backend & Frontend): 

Folder restoBM/backend adalah Pure Laravel (PHP 8.3):

Menggunakan Laravel versi terbaru.
Menggunakan Eloquent ORM, Database Migrations, Seeders, Controllers, Form Request Validation, dan Database Transactions (DB::transaction).
Bertindak sebagai REST API Server yang mengolah data bisnis restoran, stok barang, perhitungan piring biro, dan menyimpan transaksi ke PostgreSQL.
Folder restoBM/frontend adalah Client Web App:

Dibangun menggunakan HTML5, CSS murni (Vanilla CSS), dan JavaScript murni (Vanilla JS) dengan bundler/dev-server Vite.
Tidak mencampur kode PHP dengan tampilan (Blade). Frontend berkomunikasi dengan backend Laravel melalui panggilan API (fetch / JSON).
2. Apakah Berpengaruh dengan Konsistensi?
Sangat Tidak. Justru arsitektur ini jauh lebih konsisten dan aman, karena:

Konsistensi Data Terjaga: Semua aturan bisnis penting (harga menu, potongan VIP, rumus piring biro Rp 2.000, pengurangan stok inventaris) divalidasi dan dihitung di sisi backend Laravel, bukan di browser.
Bebas Masalah Kompatibilitas: Frontend murni HTML/CSS/JS tanpa ketergantungan library pihak ketiga yang rumit, sehingga tidak ada risiko dependency conflict atau masalah CSS yang saling bertabrakan.
Bisa Dikembangkan ke Mana Saja: Karena backend-nya adalah REST API murni, di masa depan sistem ini bisa dihubungkan ke aplikasi Android/iOS kasir, tablet pelayan, atau dashboard display dapur tanpa perlu mengubah backend Laravel-nya.
3. Bahasa & Framework yang Digunakan di restoBM
Bagian	Lokasi Folder	Bahasa Pemrograman	Framework / Alat	Fungsi Utama
Backend	restoBM/backend	PHP 8.3	Laravel Framework	Otentikasi, REST API, Business Logic, Transaksi Stok, Relasi Database
Database	PostgreSQL	SQL	PostgreSQL 18	Menyimpan seluruh data master, stok, penjualan, biaya operasional
Frontend	restoBM/frontend	JavaScript (ES6+)	Vite (Dev Server)	Logika interaktif kasir, keranjang belanja, cetak struk
Styling	restoBM/frontend/src/styles	CSS3	Vanilla CSS	Desain tema restoran premium (Maroon #6B0B0B & Gold #C9A87C)
Struktur	restoBM/frontend/*.html	HTML5	Semantic HTML	Kerangka tampilan halaman (Login, Kasir, Inventaris)
4. Cara Menjalankan Sistem restoBM
Sistem ini membutuhkan 2 terminal yang berjalan berdampingan:

Terminal 1: Menjalankan Backend Laravel
Buka terminal/Command Prompt, lalu ketik:

bash
cd D:\Projek\pos_restoran\restoBM\backend
path C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe artisan serve
php artisan serve
Backend Laravel akan aktif di http://127.0.0.1:8000.

Terminal 2: Menjalankan Frontend Vite
Buka terminal/Command Prompt kedua, lalu ketik:

bash
cd D:\Projek\pos_restoran\restoBM\frontend
npm run dev
Frontend akan aktif di http://localhost:5173.

Cara Mengakses:
Buka browser (Chrome / Edge).
Kunjungi http://localhost:5173.
Login menggunakan salah satu akun bawaan:
Kasir: kasir1 | Password: password123
Admin: admin | Password: password123
Pemilik: pemilik | Password: password123
Setelah login berhasil, Anda akan otomatis masuk ke halaman kasir.html.