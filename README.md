# Admin Template (Laravel 13 + Tailwind 4)

A professional and modern administrative dashboard template built with **Laravel 13** and **Bootstrap 5 (NiceAdmin Template)**. This project provides a solid foundation for building robust back-office applications with built-in user management, settings, and profile features.

## 🚀 Fitur Utama

- **Otentikasi & Keamanan**: Login, Logout, dan Middleware Role (Superadmin).
- **Manajemen User**: CRUD (Create, Read, Update, Delete) data pengguna lengkap dengan foto profil (avatar).
- **Manajemen Profil**: Halaman profil untuk setiap pengguna (Dashboard Show & Edit).
- **Pengaturan Aplikasi**: Pengaturan nama aplikasi, logo, kata kunci, dan deskripsi SEO melalui dashboard.
- **UI/UX Modern**: Menggunakan template NiceAdmin dengan integrasi:
    - **DataTables**: Untuk tabel data yang interaktif.
    - **SweetAlert2**: Untuk notifikasi yang cantik.
    - **Select2**: Untuk dropdown yang lebih baik.
    - **TinyMCE**: Untuk editor teks kaya.
- **Ekspor Laporan**: Dukungan ekspor data ke format PDF (menggunakan `laravel-dompdf`).
- **Lokalisasi**: Sudah dikonfigurasi menggunakan Bahasa Indonesia (`id`).

## 🎨 Kustomisasi Tema (Warna)

Template ini telah dimodifikasi agar warna tema utamanya sangat mudah diganti. Anda hanya perlu mengubah **CSS Variables** di satu tempat saja, dan seluruh elemen (Header, Footer, Tombol Primary, Sidebar Aktif, dll.) akan otomatis menyesuaikan.

1. Buka file `resources/views/layouts/app.blade.php`.
2. Cari bagian `<style>` di dalam tag `<head>`.
3. Ubah kode Hex warna pada blok `:root`:

```css
:root {
    /* ====== UBAH WARNA TEMA DI SINI ====== */
    --theme-bg: #7c3aed;    /* Warna utama tema (contoh: Ungu/Biru) */
    --theme-hover: #6d28d9; /* Warna lebih gelap untuk efek hover tombol/menu */
    --theme-text: #ffffff;  /* Warna teks di atas warna utama */
    
    --main-bg: #f6f9ff;     /* Warna background utama halaman konten */
    /* ===================================== */
}
```
4. Simpan file, lalu muat ulang (refresh) halaman pada browser Anda.

## 🔑 Kredensial Default

Setelah menjalankan seeder, Anda dapat login menggunakan akun berikut:

| Nama        | Email             | Password   | Role       |
| ----------- | ----------------- | ---------- | ---------- |
| Tamus Tahir | `tamus@gmail.com` | `password` | Superadmin |
| Joh Doe     | `admin@gmail.com` | `password` | Admin      |

## 🛠️ Stack Teknologi

- **Backend**: PHP 8.3 & Laravel 13.0
- **Frontend**: Bootstrap 5
- **Database**: SQLite (default)
- **Library Penting**:
    - `barryvdh/laravel-dompdf`
    - `laravel/tinker`
    - `pestphp/pest` (Testing)

## 💻 Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan proyek di mesin lokal Anda:

1. **Clone Repositori**:

    ```bash
    git clone <repository-url>
    cd admin-template
    ```

2. **Instal Dependensi PHP**:

    ```bash
    composer install
    ```

3. **Instal Dependensi JavaScript**:

    ```bash
    npm install
    ```

4. **Konfigurasi Lingkungan**:
   Salin file `.env.example` menjadi `.env` dan generate key:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Setup Database (SQLite)**:
   Buat file database kosong dan jalankan migrasi beserta seeder:

    ```bash
    touch database/database.sqlite
    php artisan migrate --seed
    ```

6. **Jalankan Aplikasi**:
   Anda dapat menggunakan script setup yang sudah disediakan atau menjalankan server secara manual:

    ```bash
    # Menggunakan script internal
    composer run dev

    # ATAU menjalankan secara terpisah
    php artisan serve
    npm run dev
    ```

## 📝 Script Tambahan

- `composer run setup`: Menjalankan instalasi lengkap (composer, npm, migrate, build).
- `composer run test`: Menjalankan unit testing menggunakan Pest.

## 📄 Lisensi

Proyek ini bersifat open-source di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
