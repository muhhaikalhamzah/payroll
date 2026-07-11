# DETAIL TASK 10

## 1. Deskripsi & Tujuan Task
Mengembangkan fungsionalitas manajemen Pinjaman dan Kasbon Karyawan (*Employee Loans*). Modul ini menyediakan layanan mandiri (*Employee Self-Service*) bagi karyawan untuk mengajukan dana kasbon, mengatur tenor (durasi) cicilan bulanan, melacak riwayat pelunasan, serta mengintegrasikan pemotongan otomatis (*auto-deduction*) cicilan ke dalam mesin penggajian bulanan.

## 2. Referensi PRD
- **Bab 5: Modul 11** (Pinjaman & Kasbon Karyawan)
- **Bab 6: Business Rules** (Plafon pinjaman dan batasan potongan 50% pendapatan kotor sesuai PP 36/2021)
- **Bab 9: Skema Data** (Tabel `employee_loans`)

## 3. Analisis Gap (Existing vs Target)
| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Pengajuan Pinjaman** | Tidak ada form maupun pencatatan status. | Portal pengajuan mandiri dengan fitur simulasi tenor dan validasi limit plafon maksimum. |
| **Approval Workflow** | Belum terintegrasi. | *State machine* berjenjang (DRAFT -> PENDING -> APPROVED -> DISBURSED). |
| **Arsitektur** | - | Pemanfaatan Trait `Approvable` (Task 6), `Auditable` (Task 9), dan Trigger Notifikasi. |

## 4. Strategi Migrasi Data Existing
- Merupakan fitur baru; tidak diperlukan migrasi skema tabel lama.

## 5. Perubahan Struktur Database
- **Penyempurnaan Tabel `employee_loans`**:
  - `id`, `employee_id` (foreignId).
  - `request_date` (date).
  - `reason` (text).
  - `total_amount` (decimal) -> Total dana yang dipinjam.
  - `requested_tenor_months` (integer) -> Durasi cicilan (contoh: 3 bulan).
  - `monthly_installment` (decimal) -> Cicilan bulanan (total_amount / tenor).
  - `remaining_balance` (decimal) -> Sisa hutang yang belum lunas.
  - `status` (enum: 'DRAFT', 'PENDING_FINANCE', 'APPROVED', 'REJECTED', 'DISBURSED', 'COMPLETED').
  - `approved_by` (foreignId refer ke `users.id`, nullable).
  - `approved_at` (timestamp, nullable).
  - `timestamps`.

## 6. Perubahan pada Model
- **Model `EmployeeLoan`**:
  - Terapkan relasi standar `belongsTo` ke `Employee`.
  - Gunakan trait `Approvable` agar jejak *approval* tercatat di tabel log *polymorphic*.
  - Gunakan trait `Auditable` agar pencairan dana tercatat riwayatnya (komplain/investigasi).
  - Gunakan trait bawaan Laravel `Notifiable` jika entitas ini bisa memicu notifikasi spesifik.

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `EmployeeLoanController`.
- **Validasi Plafon Pinjaman (Form Request)**:
  - Validasi nominal (`total_amount`) tidak boleh melebihi aturan perusahaan (misal batas *hardcode* limit 3x `basic_salary` milik `employee` tersebut). Tolak *request* dengan return HTTP 422 jika melebihi batas.
- **Validasi Batas 50% (Saat Validasi Payroll / Dry Run)**:
  - Ini adalah *business rule* krusial (PP 36/2021). Walaupun pemotongan dieksekusi di modul *Payroll* (Task 5), logikanya erat terkait modul ini. Total semua komponen deduksi (Pajak + BPJS + Cicilan Pinjaman Aktif + Denda) *TIDAK BOLEH* lebih dari 50% dari *Gross Pay*. 
  - Jika cicilan menyebabkan angka ini tembus, *Payroll Run* akan melabeli payslip tersebut dengan status *Warning/Error*, dan HR wajib mengintervensi (misal: merestrukturisasi tenor sisa hutang karyawan).
- **State Guard & Pencairan (Disbursement)**:
  - *Approval* dikendalikan oleh Role `Finance Admin`.
  - Hanya pinjaman yang sudah mencapai status `DISBURSED` (Dana benar-benar telah ditransfer Finance ke Karyawan) yang boleh dipotong (*auto-deduct*) di siklus payroll selanjutnya.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php`**:
  - `view-employee-loans-own`, `view-employee-loans-all`
  - `submit-employee-loans` (Employee)
  - `approve-employee-loans` (Finance/HR)
- **`EmployeeLoanSeeder.php`**:
  - Membuat simulasi 3 kasus pinjaman:
    1. Pinjaman lunas (`status` = COMPLETED, `remaining_balance` = 0).
    2. Pinjaman berjalan (`status` = DISBURSED, sisa cicilan > 0).
    3. Pengajuan baru (`status` = PENDING_FINANCE).

## 9. Acceptance Criteria
- [ ] Karyawan sukses mem-post pengajuan kasbon asalkan nominalnya mematuhi aturan maksimal Plafon (contoh: 3x gaji).
- [ ] Proses validasi persetujuan direkam di `approval_logs` secara lengkap.
- [ ] Aksi persetujuan otomatis memicu notifikasi In-App kepada karyawan yang bersangkutan.
- [ ] Status berubah menjadi COMPLETED secara sistematis jika `remaining_balance` sudah menyentuh titik Rp 0 usai pemotongan *payroll*.

## 10. Testing
- **Test Cases**:
  - Test validasi batas plafon: Uji dengan nominal 4x gaji dan verifikasi sistem me-return 422 Unprocessable Entity.
  - Test State Guard: Memastikan Finance hanya bisa melakukan "Disburse" jika status pinjaman adalah "APPROVED".

## 11. Risiko & Catatan
- **Pembulatan Desimal Bulanan**: Nilai `total_amount` yang dibagi `requested_tenor_months` menghasilkan desimal berulang (misal Rp 1.000.000 / 3 = 333.333,33).
  *Mitigasi*: Bebankan sisa pembulatan pada cicilan di bulan TERAKHIR (Bulan 1: 333.333, Bulan 2: 333.333, Bulan 3: 333.334) guna menghindari saldo sisa (-0.01) atau gantung di database.
