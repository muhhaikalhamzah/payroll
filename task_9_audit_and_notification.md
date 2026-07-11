# DETAIL TASK 9

## 1. Deskripsi & Tujuan Task
Membangun infrastruktur pelacakan (*Audit Logging*) dan pusat sistem notifikasi (*Notification System*). Audit Log wajib diimplementasikan untuk memenuhi standar kepatuhan (*compliance*) yang merekam setiap perubahan data sensitif, menjadikannya *immutable* (tidak dapat diubah/dihapus). Sistem notifikasi digunakan untuk memberikan peringatan lintas modul secara terpusat (contoh: persetujuan cuti, lembur, dan *payroll*).

## 2. Referensi PRD
- **Bab 9: Skema Data** (Tabel `audit_logs`, `notifications`)
- **Bab 10: Non-Functional Requirements** (Audit & Kepatuhan, seluruh manipulasi data tersimpan di `audit_logs`).

## 3. Analisis Gap (Existing vs Target)
| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Audit Logging** | Tidak ada mekanisme pelacakan perubahan. | Mekanisme observer otomatis yang mencatat *old values* dan *new values*. |
| **Sistem Notifikasi** | Belum diimplementasikan secara terpusat. | Tabel `notifications` bawaan Laravel dengan saluran (channel) *database* dan *mail*. |
| **Testing** | - | Pengujian otomatisasi pembuatan log ketika entitas sensitif diperbarui. |

## 4. Strategi Migrasi Data Existing
- Tidak diperlukan migrasi, tabel bersifat suplementer dan mulai mencatat aktivitas sejak modul di-_deploy_.

## 5. Perubahan Struktur Database
- **`xxxx_xx_xx_xxxxxx_create_audit_logs_table.php`**
  - `id`
  - `user_id` (foreignId refer ke `users.id`, nullable jika sistem yang melakukan).
  - `action` (string, misal 'CREATED', 'UPDATED', 'DELETED').
  - `auditable_type`, `auditable_id` (morphs, model apa yang diubah).
  - `old_values`, `new_values` (json, nullable).
  - `ip_address` (string, nullable).
  - `user_agent` (string, nullable).
  - `created_at` (timestamp, tanpa `updated_at` untuk menegaskan sifat *immutable*).
- **`xxxx_xx_xx_xxxxxx_create_notifications_table.php`**
  - Menggunakan perintah bawaan Laravel: `php artisan notifications:table`.

## 6. Perubahan pada Model
- **Model `AuditLog`**: Bersifat Read-Only. Tidak boleh ada proses pembaruan/penghapusan.
- **Pembuatan Trait `Auditable`**:
  - Pasang trait ini pada model sensitif seperti `EmployeeBankAccount`, `Payslip`, `Employee`, `PayrollRun`, dsb.
  - Trait ini me-register *Eloquent Observers* (`created`, `updated`, `deleted`, `restored`) untuk otomatis men-dump perubahan ke tabel `audit_logs`.
- **Model `User`**: Tambahkan trait bawaan Laravel `Notifiable` (jika belum ada).

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller `AuditLogController`**: 
  - Hanya memiliki fungsionalitas **READ** (`index`, `show`).
  - Tidak ada aksi *create, update, delete*.
- **Controller `NotificationController`**:
  - Endpoints untuk memanajemen In-App Notification: `index` (lihat list notif), `markAsRead` (tandai sudah dibaca).
- **Pembuatan Class Notifikasi Lintas Modul**:
  - `LeaveRequestSubmitted`, `PayrollRunPending`, `OvertimeApproved`, dsb, dengan pengiriman ganda via channel `database` (in-app UI) dan `mail`.
- **Otorisasi (Gate & Policy)**:
  - `view-audit-logs` HANYA boleh dipegang oleh role `Super Admin` demi keamanan investigasi (HR/Finance dilarang memanipulasi atau melihat log sistem secara bebas).
  - Karyawan hanya bisa melihat notifikasi mereka sendiri (terisolasi by user ID bawaan Laravel).

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php`**:
  - Tambahkan permission: `view-audit-logs`.
- **(Tidak perlu seeder log data spesifik, log akan otomatis tergenerate saat seeder modul lain berjalan pasca-trait dipasang)**.

## 9. Acceptance Criteria
- [ ] Perubahan data sensitif pada modul yang memakai trait `Auditable` otomatis tercatat ke database tanpa intervensi manual di controller.
- [ ] Log mencatat `old_values` dan `new_values` secara akurat (format JSON).
- [ ] Tersedia endpoint/halaman bagi Super Admin untuk memantau Audit Logs.
- [ ] Aksi persetujuan (approval) memicu notifikasi In-App bagi pihak yang bersangkutan.

## 10. Testing
- **Test Cases**:
  - Memperbarui nomor rekening pegawai dan mem-verifikasi bahwa baris baru muncul di `audit_logs` dengan format JSON yang memuat nomor lama dan baru.
  - Mengirim notifikasi dan memastikan tabel `notifications` terisi, serta status `read_at` bisa diupdate.

## 11. Risiko & Catatan
- **Database Bloat (Pembengkakan Data)**: Pencatatan rekaman setiap operasi akan membuat tabel `audit_logs` membengkak pesat dalam skala tahunan. *Mitigasi*: Implementasikan mekanisme *partitioning* pada tabel, atau sediakan fitur *Archiving* ke cold storage (S3/CSV) untuk data log yang berumur lebih dari 3 tahun.
- **Dependency**: Modul ini harus dikerjakan SEDINI mungkin (direkomendasikan setelah/berbarengan dengan Task 1) agar semua *seeding* dan transaksi pada Task 2-8 otomatis terekam oleh Trait `Auditable`.
