# DETAIL TASK 8

## 1. Deskripsi & Tujuan Task
Mengembangkan modul Manajemen Cuti & Izin (Leave Management). Modul ini mencakup definisi tipe cuti, pencatatan dan akrual (penambahan otomatis) saldo cuti tahunan/bulanan, serta proses pengajuan cuti oleh karyawan yang membutuhkan persetujuan berjenjang (Manager lalu HR).

## 2. Referensi PRD
- **Bab 2: Modul 4** (Cuti & Izin)
- **Bab 7: Approval Workflow** (State machine cuti)
- **Bab 9: Skema Data** (Tabel `leave_types`, `leave_balances`, `leave_requests`)

## 3. Analisis Gap (Existing vs Target)
| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Manajemen Cuti** | Belum ada fungsionalitas pencatatan cuti. | Pengajuan cuti *self-service* oleh karyawan dengan validasi sisa saldo dan kalender tim. |
| **Arsitektur** | - | Menggunakan trait `Approvable` (polymorphic) dari Task 6 untuk *approval workflow*. |
| **Testing** | - | Pengujian logika pemotongan saldo cuti (deduction) dan akrual. |

## 4. Strategi Migrasi Data Existing
- Tidak ada data existing yang perlu dimigrasi.

## 5. Perubahan Struktur Database
- **`xxxx_xx_xx_xxxxxx_create_leave_types_table.php`**
  - `id`, `name` (string, misal: "Cuti Tahunan", "Sakit").
  - `max_days` (integer).
  - `is_carry_forward` (boolean, apakah sisa saldo bisa dibawa ke tahun depan).
  - `timestamps`.
- **`xxxx_xx_xx_xxxxxx_create_leave_balances_table.php`**
  - `id`, `employee_id` (foreignId refer ke `employees.id`, cascade).
  - `leave_type_id` (foreignId refer ke `leave_types.id`).
  - `year` (integer).
  - `balance` (integer/decimal, saldo yang berhak didapat).
  - `used` (integer/decimal, saldo yang sudah terpakai).
  - `timestamps`.
- **`xxxx_xx_xx_xxxxxx_create_leave_requests_table.php`**
  - `id`, `employee_id` (foreignId).
  - `leave_type_id` (foreignId).
  - `start_date`, `end_date` (date).
  - `reason` (text).
  - `status` (enum: 'DRAFT', 'PENDING_MANAGER', 'PENDING_HR', 'APPROVED', 'REJECTED').
  - `timestamps`.

## 6. Perubahan pada Model
- **Model `LeaveType`, `LeaveBalance`, `LeaveRequest`**:
  - Terapkan relasi standar (`belongsTo` Employee, dll).
  - **`LeaveRequest`**: Gunakan trait `Approvable` (diperkenalkan di Task 6) agar terintegrasi dengan tabel `approvals` dan `approval_logs`.

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `LeaveTypeController`, `LeaveBalanceController`, `LeaveRequestController`.
- **Logika Akrual Saldo**:
  - Buat Laravel Console Command / Scheduled Job (berjalan 1 Januari atau per bulan anniversary karyawan) untuk men-generate `leave_balances` baru bagi setiap karyawan aktif, memindahkan (carry over) sisa cuti jika `is_carry_forward` true.
- **Validasi Pengajuan Cuti (Gate/Policy & Form Request)**:
  - Validasi bahwa rentang tanggal `start_date` dan `end_date` tidak tumpang tindih dengan cuti sebelumnya.
  - Validasi bahwa durasi hari kerja dalam rentang tersebut **tidak melebihi** sisa saldo (`balance - used`) di tabel `leave_balances`.
- **Approval Workflow**:
  - State Guard: Karyawan -> DRAFT -> Submit -> PENDING_MANAGER. Manager Approve -> PENDING_HR. HR Approve -> APPROVED. 
  - Saat status berubah menjadi APPROVED, eksekusi pemotongan kolom `used` di tabel `leave_balances` di dalam DB Transaction.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php`**:
  - Tambahkan permission: `manage-leave-types`, `manage-leave-balances`.
  - Tambahkan permission: `view-leave-requests`, `submit-leave-requests`, `approve-leave-requests`.
- **`LeaveSeeder.php`**:
  - Generate tipe cuti standar (Tahunan 12 hari).
  - Generate saldo cuti tahun berjalan untuk setiap dummy employee.

## 9. Acceptance Criteria
- [ ] Karyawan dapat mengajukan cuti jika saldo mencukupi, dan ditolak secara sistem jika saldo tidak cukup.
- [ ] Alur persetujuan cuti berjenjang (Manager -> HR) berjalan menggunakan skema *polymorphic approvals*.
- [ ] Terdapat fitur kalender (UI) untuk melihat cuti tim agar manajer terhindar dari *blind-approval*.
- [ ] Saldo cuti terpotong otomatis hanya ketika status cuti sudah 'APPROVED'.

## 10. Testing
- **Test Cases**:
  - Simulasi *concurrency*: Mengajukan dua cuti bersamaan yang totalnya melebihi saldo. Harus digagalkan oleh *pessimistic locking* atau transaksi DB.
  - Simulasi persetujuan HR yang memicu update nilai `used` di tabel `leave_balances`.

## 11. Risiko & Catatan
- **Inkonsistensi Saldo**: Memotong saldo saat status 'PENDING' vs 'APPROVED' sering jadi perdebatan. Praktik terbaik: Potong saat 'APPROVED', namun hitung juga cuti 'PENDING' sebagai *on-hold balance* dalam validasi form pengajuan agar saldo tidak bocor (*over-request*).
- **Dependency**: Modul ini harus dikerjakan SETELAH Task 6 selesai, karena membutuhkan infrastruktur Trait `Approvable` dari Workflow Engine.
