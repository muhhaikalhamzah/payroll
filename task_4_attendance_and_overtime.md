# DETAIL TASK 4

## 1. Deskripsi & Tujuan Task
Membuat modul untuk manajemen kehadiran (absensi) dan pengajuan lembur. Fitur utama mencakup kemampuan mengimpor data absensi massal (biasanya dari mesin absensi/fingerprint) dalam format Excel/CSV, serta alur pengajuan lembur yang memiliki *state machine* persetujuan dari atasan.

## 2. Referensi PRD
- **Bab 9: Skema Data** (Tabel `attendance_records`, `overtime_requests`)
- **Bab 7: Approval Workflow** (State machine cuti/lembur)
- **Bab 2: Modul 3** (Absensi & Waktu Kerja)

## 3. Analisis Gap (Existing vs Target)

| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Ketersediaan Modul** | Tidak ada pencatatan kehadiran & lembur. | Fitur absensi (import) & pengajuan lembur berbasis form dengan alur approval. |
| **Arsitektur** | - | Integrasi dengan modul import file Excel/CSV. |
| **Testing** | - | Pengujian validasi import baris per baris dan pengujian transisi status lembur. |

## 4. Strategi Migrasi Data Existing
- Tabel baru, tidak memerlukan migrasi data existing.

## 5. Perubahan Struktur Database
- **`xxxx_xx_xx_xxxxxx_create_attendance_records_table.php`**
  - `id`, `employee_id` (foreignId refer ke `employees.id`, cascade delete).
  - `date` (date).
  - `clock_in`, `clock_out` (time/datetime, nullable).
  - `status` (enum: hadir, alfa, izin, sakit, cuti).
  - `remarks` (string, nullable).
  - *Unique constraint* pada `(employee_id, date)` untuk mencegah data ganda di hari yang sama.
- **`xxxx_xx_xx_xxxxxx_create_overtime_requests_table.php`**
  - `id`, `employee_id` (foreignId).
  - `date` (date).
  - `duration_minutes` (integer).
  - `reason` (text).
  - `status` (enum: 'DRAFT', 'PENDING_MANAGER', 'APPROVED', 'REJECTED'). Default: 'DRAFT'.
  - `approved_by` (foreignId refer ke `users.id`, nullable).
  - `timestamps`.

## 6. Perubahan pada Model
- **Model `AttendanceRecord`**:
  - Fillable: `['employee_id', 'date', 'clock_in', 'clock_out', 'status', 'remarks']`.
  - Relasi `employee()`: `belongsTo(Employee::class)`.
- **Model `OvertimeRequest`**:
  - Fillable: `['employee_id', 'date', 'duration_minutes', 'reason', 'status', 'approved_by']`.
  - Relasi `employee()`: `belongsTo(Employee::class)`.
  - Relasi `approver()`: `belongsTo(User::class, 'approved_by')`.

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `AttendanceController` (termasuk method `import()`), `OvertimeRequestController`.
- **Fitur Import Absensi**:
  - Format file yang diterima: CSV / Excel (`.xlsx`).
  - Kolom wajib: NIK, Tanggal, Jam Masuk, Jam Keluar.
  - Validasi: Baris yang invalid (contoh NIK tidak ditemukan) dilewati atau dicatat ke dalam log error (tidak menggagalkan seluruh import, pendekatan *graceful failure*).
- **Validasi Overtime terhadap Attendance**:
  - Saat lembur diajukan pada tanggal tertentu, sistem *WAJIB* memvalidasi bahwa terdapat record di `attendance_records` untuk pegawai tersebut di tanggal yang sama (`AttendanceRecord::where('employee_id', $employeeId)->where('date', $date)->exists()`).
- **Validasi State/Status Sebelum Aksi**:
  - Manager hanya bisa menyetujui (Approve/Reject) jika `status` lembur adalah `PENDING_MANAGER`. Divalidasi secara ketat di Controller.
- **Otorisasi (Gate & Policy) & Null-Safety**:
  - Policy membatasi Employee hanya bisa melihat absensi dan lemburnya sendiri, diakses menggunakan *null-safe* operator `auth()->user()?->employee?->id`.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php` (Penambahan)**:
  - `view-attendances`, `import-attendances`, `manage-attendances`
  - `view-overtime-requests`, `submit-overtime-requests`, `approve-overtime-requests` (mengikuti konvensi penamaan plural).
- **`AttendanceOvertimeSeeder.php`**:
  - Generate absensi acak untuk bulan berjalan bagi dummy employee.
  - Generate dummy lembur (beberapa status PENDING, beberapa APPROVED).

## 9. Acceptance Criteria
- [ ] Tersedia halaman untuk upload dan import CSV/Excel absensi dengan laporan hasil sukses/gagal per baris.
- [ ] Pegawai dapat mengajukan lembur (status awal DRAFT, berlanjut ke PENDING_MANAGER).
- [ ] Manajer dapat melakukan Approve/Reject terhadap pengajuan lembur, dicegah memproses lembur yang bukan berstatus PENDING_MANAGER.
- [ ] Pengajuan lembur divalidasi dan akan ditolak backend jika pegawai belum memiliki data absensi pada tanggal pengajuan lembur (sesuai kesepakatan Bab 9 PRD).
- [ ] Gate & Policy mengamankan fungsionalitas.

## 10. Testing
- **File Test**: `tests/Feature/AttendanceOvertimeTest.php`.
- **Test Cases**:
  - Uji *state machine* lembur: memastikan persetujuan ditolak jika status sudah APPROVED/REJECTED.
  - Uji validasi cross-check: mencoba submit lembur di tanggal tanpa absen, dipastikan muncul error validasi.

## 11. Risiko & Catatan
- **Timeout pada Import Data**: Import file absensi berkapasitas besar berpotensi memicu *timeout* PHP. *Mitigasi*: Eksekusi parsing file menggunakan Laravel Job/Queue di background.
