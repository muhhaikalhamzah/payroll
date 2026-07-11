# DETAIL TASK 5

## 1. Deskripsi & Tujuan Task
Membangun *core engine* sistem penggajian (payroll run) yang melakukan kalkulasi otomatis gaji per karyawan. Proses ini melibatkan pengumpulan gaji pokok, perhitungan komponen allowance/deduction, kalkulasi uang lembur, pemotongan PPh 21 dengan metode TER, pemotongan BPJS, serta pemotongan cicilan pinjaman jika ada. Fitur ini dirancang untuk menangani pemrosesan massal (batch processing) secara aman.

## 2. Referensi PRD
- **Bab 6: Business Rules & Formula Perhitungan** (Gross to net, prorata, lembur, TER PPh 21, BPJS).
- **Bab 9: Skema Data** (Tabel `payroll_runs`, `payslips`, `payslip_components`, `tax_records`, `bpjs_records`, `employee_loans`).
- **Modul 5, 6, 7** (Payroll Processing, Tax, BPJS).

## 3. Analisis Gap (Existing vs Target)

| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Kalkulasi & Core Engine** | Belum ada logic bisnis kalkulasi. | Engine kalkulasi step-by-step terstruktur dan tereksekusi secara asinkron menggunakan Laravel Job. |
| **Arsitektur** | - | Penggunaan layer Service (misal `PayrollCalculatorService`) sangat disarankan untuk memisahkan logic dari Controller. |
| **Testing** | - | *Unit testing* intensif dengan contoh skenario nominal gaji riil (Base Salary vs Net Pay). |

## 4. Strategi Migrasi Data Existing
- Tidak diperlukan migrasi, tabel baru.

## 5. Perubahan Struktur Database
- **`xxxx_xx_xx_xxxxxx_create_employee_loans_table.php`**
  - `id`, `employee_id` (foreignId).
  - `total_amount`, `remaining_amount`, `monthly_installment` (decimal).
- **`xxxx_xx_xx_xxxxxx_create_payroll_runs_table.php`**
  - `id`, `period_month`, `period_year` (integer).
  - `status` (enum: 'DRAFT', 'PENDING_FINANCE', 'APPROVED', 'PAID').
  - `created_by` (foreignId refer ke `users.id`).
- **`xxxx_xx_xx_xxxxxx_create_payslips_table.php`**
  - `id`, `payroll_run_id` (foreignId), `employee_id` (foreignId).
  - `basic_salary`, `total_allowances`, `total_deductions`, `net_pay` (decimal).
  - `status` (enum: 'DRAFT', 'FINAL').
- **`xxxx_xx_xx_xxxxxx_create_payslip_components_table.php`**
  - `id`, `payslip_id` (foreignId, cascade delete).
  - `name` (string), `amount` (decimal), `type` (enum: 'allowance', 'deduction').
- **`xxxx_xx_xx_xxxxxx_create_tax_records_table.php`** & **`bpjs_records_table.php`**
  - Menyimpan rincian pemotongan PPh 21 (TER category, pph21_amount) dan BPJS (jht, jp, jkk, jkm, kesehatan) berelasi dengan `payslip_id`.

## 6. Perubahan pada Model
- **Model `PayrollRun`**: Relasi `hasMany(Payslip::class)`.
- **Model `Payslip`**: Relasi `belongsTo(Employee::class)`, `hasMany(PayslipComponent::class)`, `hasOne(TaxRecord::class)`, `hasOne(BpjsRecord::class)`.
- **Model `EmployeeLoan`**: Relasi `belongsTo(Employee::class)`.

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `PayrollRunController`.
- **Urutan Kalkulasi & Business Rules (Sesuai PRD Bab 6)**:
  1. **Base Salary & Prorata**: Rumus Prorata: `(Hari Kerja Aktual / Hari Kerja Sebulan) * Gaji Pokok`. Diaplikasikan jika tanggal masuk/resign di bulan berjalan.
  2. **Allowances & Overtime**: Tambahkan komponen tunjangan tetap dan variabel. Rumus Lembur per PRD: `Durasi Lembur (jam) * (1/173) * Gaji Pokok`.
  3. **Gross Pay**: Gaji Pokok (setelah prorata) + Total Tunjangan + Total Lembur.
  4. **Pinjaman & Absen**: Kurangi `monthly_installment` dari `employee_loans` dan pemotongan alfa/absen (tanpa izin).
  5. **BPJS (Asumsi Standar)**: Hitung BPJS berdasarkan Gross Pay / Base Salary (sesuai aturan JHT 2%, JP 1%, Kesehatan 1% ditanggung pegawai).
  6. **PPh 21 (TER)**: Tentukan Kategori TER (A/B/C) berdasarkan status PTKP. Tentukan tarif % dari tabel TER bulanan berdasarkan rentang Bruto. Rumus: `Tarif TER x Penghasilan Bruto`.
  7. **Net Pay**: Gross Pay - (Pinjaman + Absen + BPJS + PPh 21).
- **Proses Batch & Simulasi**:
  - Untuk memenuhi NFR (1000 karyawan < 10 menit), *Payroll Run* dibuat sebagai Job yang dilempar ke Redis/Database Queue (`Dispatchable`, `Queueable`). Controller hanya membalas "Proses Kalkulasi Sedang Berjalan".
- **Otorisasi & Keamanan**:
  - Validasi Status: Sebuah `PayrollRun` hanya bisa di-generate ulang / kalkulasi ulang jika statusnya masih `DRAFT`.
  - Akses detail Payslip diproteksi menggunakan **Route Model Binding dan Policy Check** agar pengguna tidak dapat menebak ID payslip (`/payslips/999`) secara sembarangan.
  - Akses null-safe: `$payrollRun->created_by === auth()->user()?->id`.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php`**: 
  - Tambahkan `view-payroll-runs`, `create-payroll-runs`, `manage-employee-loans`. (Penamaan menggunakan plural yang konsisten).
- **`PayrollSeeder.php`**:
  - Generate data simulasi peminjaman pegawai.
  - WAJIB men-generate data `payroll_runs` untuk **3-6 periode bulan terakhir** secara historis (rentang waktu yang cukup), sehingga Dashboard di Task 7 memiliki data tren grafis yang valid secara visual.

## 9. Acceptance Criteria
- [ ] Proses *Generate Payroll* tereksekusi di background (menggunakan Laravel Job/Queue).
- [ ] Akses URL ke data penggajian terproteksi sempurna dengan Route Model Binding & Gate/Policy.
- [ ] Kalkulasi Prorata, Lembur, dan PPh 21 TER mengikuti formula ketat sesuai PRD Bab 6.
- [ ] Kalkulasi payroll dilarang (diblokir) apabila status periode sudah melewati tahap `DRAFT`.

## 10. Testing
- **Test Cases**:
  - Test Prorata calculation berdasarkan formula (Hari Kerja Aktual / Hari Kerja Sebulan) * Gaji Pokok.
  - Test perlindungan Route Model Binding dan IDOR (gagal mengakses ID Payslip pengguna lain dengan return 403/404).

## 11. Risiko & Catatan
- **Performance bottleneck & Timeout**: Hitungan ratusan loop di PHP bisa mati di tengah jalan. *Mitigasi*: Penggunaan Queue (Chunking per 100 karyawan per Job) sifatnya absolut.
- **Rounding Error (Selisih Pembulatan)**: Kesalahan pembulatan pajak PPh 21. *Mitigasi*: Simpan nilai dalam bentuk *integer* (dalam sen/rupiah tanpa desimal di DB) atau Decimal(15,2) menggunakan library `bcmath`.
