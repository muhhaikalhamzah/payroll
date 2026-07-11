# DETAIL TASK 7

## 1. Deskripsi & Tujuan Task
Mengembangkan fungsionalitas pelaporan akhir yang mencakup pencetakan dan distribusi Slip Gaji (PDF), pembuatan ringkasan penggajian, *dashboard* analitik, serta fasilitas ekspor format pajak e-Bupot. Ini adalah siklus terakhir dari sistem penggajian (post-processing).

## 2. Referensi PRD
- **Bab 8: Role-Based Access Control (RBAC) Matrix**.
- **Modul 8** (Slip Gaji & Pelaporan).
- **Bab 9: Skema Data** (Memanfaatkan data dari entitas Task 5: `payroll_runs` & `payslips`).

## 3. Analisis Gap (Existing vs Target)
| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Reporting & Export** | Belum ada fitur report dan PDF generation. | Output PDF untuk Slip, CSV terstandar untuk e-Bupot, dan analitik via *Dashboard*. |
| **Arsitektur** | Hasil inspeksi: `barryvdh/laravel-dompdf` sudah tercantum di `composer.json` bawaan template/aplikasi. | Memanfaatkan package `dompdf` bawaan untuk merender View HTML menjadi PDF file. |
| **Testing** | - | Pengujian aksesibilitas dokumen (`view-payslips-own` vs `view-payslips-all`) serta validasi format file export. |

## 4. Strategi Migrasi Data Existing
- Tidak diperlukan migrasi struktur database.

## 5. Perubahan Struktur Database
- Tidak ada modifikasi skema/tabel baru secara signifikan, murni pembacaan (Read-only) data hasil proses kalkulasi.

## 6. Perubahan pada Model
- Tidak ada pembuatan entitas baru.

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `ReportController`, perluasan method di `DashboardController`, `PayslipController`.
- **Validasi Status Ekspor & PDF (Guard)**:
  - Ekspor e-Bupot maupun download PDF Slip Gaji secara resmi **HANYA BISA** dilakukan jika entitas `payroll_run` terkait memiliki `status = 'PAID'` (atau 'COMPLETED'). Divalidasi secara eksplisit di controller sebelum me-return file untuk mencegah kebocoran data draft.
- **Ekspor e-Bupot (CSV)**:
  - Format output CSV disesuaikan dengan template standar DJP.
- **Keamanan & Otorisasi Akses Sensitif (IDOR Prevention)**:
  - Download PDF sangat rawan IDOR. Hindari akses langsung seperti `/payslips/1/pdf`. Gunakan **Signed URL** Laravel, ATAU validasi ketat menggunakan Policy (via Route Model Binding) di Controller `PayslipController@showPdf` yang mengecek apakah user login adalah pemilik payslip tersebut atau seorang Admin.
  - **Null-Safety**: Pengecekan ID harus menggunakan null-safe operator: `$query->where('employee_id', auth()->user()?->employee?->id)`. Jika `$employee` null (misal akun Super Admin yang tidak terkait pegawai), query harus dibatasi oleh Permission Admin (`view-payslips-all`), bukan *crash* karena null.
- **Dashboard Analitik**:
  - Menampilkan grafik dari seeder historis (rentang 3-6 bulan) yang dibuat di Task 5.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php`**:
  - Tambahkan permission: `view-payslips-own` (untuk role Employee/Semua).
  - Tambahkan permission: `view-payslips-all`, `export-reports`, `view-dashboard-analytics`.
- *(Data historis beruntun selama 3-6 bulan wajib digenerate oleh `PayrollSeeder` dari Task 5 untuk keperluan tampilan dashboard tren ini).*

## 9. Acceptance Criteria
- [ ] Fitur unduh slip gaji PDF terproteksi dari akses tak berizin menggunakan Policy atau Signed URL.
- [ ] Endpoint ekspor PDF / e-Bupot menolak pemrosesan (throw error) jika status *Payroll Run* belum PAID.
- [ ] Pengecekan `$user->employee->id` berjalan dengan Null-Safety Operator PHP 8 tanpa memicu crash.
- [ ] Halaman *Dashboard* menampilkan grafik analitik tren agregat dari data payroll historis (3-6 bulan ke belakang).

## 10. Testing
- **Test Cases**:
  - Uji IDOR: *Mocking* user login lalu mengakses ID payslip orang lain harus mengembalikan *403 Forbidden*.
  - Uji perlindungan akses: Mencoba men-download PDF payslip dari *Payroll Run* yang masih bersatus DRAFT harus mengembalikan error.

## 11. Risiko & Catatan
- **Overhead Memory Generate PDF**: Merender HTML ke PDF memakan Memory RAM tinggi. *Mitigasi*: Gunakan antrian (Queue) untuk men-generate bulk PDF lalu ZIP, dan jangan render semuanya *on the fly*.
