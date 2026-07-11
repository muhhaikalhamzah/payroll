# DETAIL TASK 6

## 1. Deskripsi & Tujuan Task
Membangun alur persetujuan (approval workflow) berjenjang untuk pemrosesan gaji, dari HR yang melakukan pengajuan hingga Finance yang menyetujui dan merealisasikan pembayaran. Sistem didesain menggunakan pendekatan tabel *polymorphic* agar modul persetujuan ini *reusable* untuk entitas lain, seperti pengajuan lembur atau cuti.

## 2. Referensi PRD
- **Bab 7: Approval Workflow** (State machine Payroll Run).
- **Bab 9: Skema Data** (Tabel `approvals`, `approval_logs`).

## 3. Analisis Gap (Existing vs Target)

| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Workflow Engine** | Tidak ada. | State machine berbasis *polymorphic* dengan pencatatan log lengkap. |
| **Arsitektur** | - | Penggunaan Trait (misal `Approvable`) pada model yang membutuhkan persetujuan, dan Laravel Notifications. |
| **Testing** | - | *Integration test* simulasi proses lemparan data antar role (HR ke Finance). |

## 4. Strategi Migrasi Data Existing
- Tabel baru, tidak memerlukan migrasi data existing.

## 5. Perubahan Struktur Database
- **`xxxx_xx_xx_xxxxxx_create_approvals_table.php`**
  - `id`
  - `approvable_type`, `approvable_id` (morphs, index untuk polimorfisme).
  - `status` (string, menampung status dinamis misal 'PENDING_FINANCE', 'APPROVED').
  - `approver_id` (foreignId refer ke `users.id`, opsional).
  - `notes` (text, nullable).
  - `timestamps`.
- **`xxxx_xx_xx_xxxxxx_create_approval_logs_table.php`**
  - `id`
  - `approval_id` (foreignId refer ke `approvals.id`, cascade delete).
  - `actor_id` (foreignId refer ke `users.id`, user yang melakukan tindakan).
  - `action` (string, misal: 'SUBMITTED', 'APPROVED', 'REJECTED').
  - `comments` (text, nullable).
  - `timestamps`.

## 6. Perubahan pada Model
- **Model `Approval`**: Relasi `morphTo('approvable')`, relasi `hasMany(ApprovalLog::class)`.
- **Model `ApprovalLog`**: Relasi `belongsTo(Approval::class)`, relasi `belongsTo(User::class, 'actor_id')`.
- **Pembuatan Trait `Approvable`**:
  - Berisi method bantuan: `$this->morphMany(Approval::class, 'approvable')`, `$this->submitForApproval()`, `$this->approve()`.

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `ApprovalController`.
- **State Machine Payroll Run (Validasi Eksplisit)**:
  - **DRAFT**: Dibuat oleh HR.
  - **PENDING_FINANCE**: Menunggu persetujuan Finance. 
    *(Guard/Validasi Status: Aksi "Submit" HANYA BISA dieksekusi oleh HR Admin jika status `payroll_run` BENAR-BENAR masih 'DRAFT'. Divalidasi secara eksplisit di controller sebelum memproses)*.
  - **APPROVED**: Disetujui Finance. 
    *(Guard/Validasi Status: Aksi "Approve" HANYA BISA dieksekusi oleh Finance Admin jika status `payroll_run` adalah 'PENDING_FINANCE')*.
  - **PAID/COMPLETED**: Realisasi transfer selesai.
- **Otorisasi Null-Safe**: Menggunakan `auth()->user()?->id` dalam logging actor action.
- **Notifikasi**: Sistem mengirim notifikasi (via In-App DB/Email) kepada user ber-role Finance Admin saat status PENDING_FINANCE dipicu.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php`**: 
  - Tambahkan `submit-payroll-runs`, `approve-payroll-runs`, `reject-payroll-runs`, `mark-payroll-runs-paid`. (Sesuai format kata-kerja-objek jamak).

## 9. Acceptance Criteria
- [ ] Fitur *Polymorphic Approval* dapat melayani transisi status untuk Payroll Run dengan mencatat riwayat tindakan di `approval_logs`.
- [ ] Sistem secara proaktif memblokir transisi *state* yang tidak wajar (misal meng-approve yang berstatus DRAFT, atau men-submit ulang yang berstatus PENDING).
- [ ] Relasi polymorphic terpasang valid di database.

## 10. Testing
- **Test Cases**:
  - Simulasi persetujuan status tidak sah (mencoba approve dokumen DRAFT -> dipastikan return error validasi / Exception).
  - Verifikasi pencatatan log lengkap.

## 11. Risiko & Catatan
- **Kompleksitas Querying Polymorphic**: *Mitigasi*: Tambahkan Composite Index pada migration tabel `approvals` untuk kolom `approvable_type` dan `approvable_id`.
- **Race Condition**: *Mitigasi*: Implementasikan *Pessimistic Locking* (`lockForUpdate()`) di dalam Controller saat mengeksekusi transisi status.
