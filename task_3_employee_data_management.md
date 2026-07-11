# DETAIL TASK 3

## 1. Deskripsi & Tujuan Task
Membuat fitur manajemen data pegawai yang komprehensif, mencakup informasi profil dasar pegawai, riwayat mutasi jabatan, data rekening bank (untuk penggajian), serta pemetaan komponen gaji spesifik per pegawai. 

## 2. Referensi PRD
- **Bab 9: Skema Data** (Tabel `employees`, `employee_position_history`, `employee_bank_accounts`, `employee_salary_components`)
- **Bab 2: Modul 1** (Manajemen Data Karyawan)

## 3. Analisis Gap (Existing vs Target)

| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Ketersediaan Modul** | Modul data pegawai belum ada sama sekali. | CRUD lengkap untuk entitas pegawai beserta riwayat dan data finansialnya. |
| **Arsitektur** | - | Standard MVC dengan relasi kompleks (1:1 ke User, 1:M ke history & rekening). |
| **Testing** | - | Pest PHP Feature Test untuk integrasi user-pegawai dan validasi mutasi. |

## 4. Strategi Migrasi Data Existing
- Tidak diperlukan migrasi atau mapping data lama karena ini adalah tabel yang benar-benar baru.

## 5. Perubahan Struktur Database
- **`xxxx_xx_xx_xxxxxx_create_employees_table.php`**
  - `id`, `nik` (string, unik).
  - `user_id` (foreignId refer ke `users.id`, nullable, unik -> relasi 1:1, user sebagai kredensial login, employee sebagai entitas HR).
  - `department_id` (foreignId refer ke `departments.id`).
  - `position_id` (foreignId refer ke `positions.id`).
  - `first_name`, `last_name`, `email`, `phone`, `hire_date`, `status` (enum: aktif/resign).
  - `timestamps`.
- **`xxxx_xx_xx_xxxxxx_create_employee_position_history_table.php`**
  - `id`, `employee_id` (foreignId refer ke `employees.id`, cascade delete).
  - `position_id` (foreignId refer ke `positions.id`).
  - `start_date`, `end_date` (nullable).
  - `timestamps`.
- **`xxxx_xx_xx_xxxxxx_create_employee_bank_accounts_table.php`**
  - `id`, `employee_id` (foreignId refer ke `employees.id`, cascade delete).
  - `bank_name`, `account_number`, `account_name`.
  - `is_primary` (boolean).
  - `timestamps`.
- **`xxxx_xx_xx_xxxxxx_create_employee_salary_components_table.php`**
  - `id`, `employee_id` (foreignId), `salary_component_id` (foreignId).
  - `amount` (decimal).
  - `timestamps`.

*(Catatan: Sinkronisasi data jabatan: kolom `position_id` di tabel `employees` harus selalu merepresentasikan jabatan aktif saat ini yang selaras dengan data terbaru di `employee_position_history`).*

## 6. Perubahan pada Model
- **Model `Employee`**:
  - Relasi `user()`: `return $this->belongsTo(User::class);`
  - Relasi `position()`, `department()`: `return $this->belongsTo(...);`
  - Relasi `positionHistories()`: `return $this->hasMany(EmployeePositionHistory::class);`
  - Relasi `bankAccounts()`: `return $this->hasMany(EmployeeBankAccount::class);`
  - Relasi `salaryComponents()`: `return $this->belongsToMany(SalaryComponent::class)->withPivot('amount');`
- **Model `EmployeePositionHistory`**, **`EmployeeBankAccount`**:
  - Relasi ke `Employee` (`belongsTo`).

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `EmployeeController`, `EmployeeBankAccountController`.
- **Null-Safety & Otorisasi Akses Sensitif**:
  - Pengaksesan data `employee` dari user yang sedang login wajib bersifat null-safe (`auth()->user()?->employee?->id`), karena user Super Admin mungkin tidak memiliki entitas `employee`.
  - **Keamanan Route Model Binding**: Data rekening bank (`EmployeeBankAccount`) bersifat sensitif. Hindari ID tebakan (IDOR). Terapkan Policy eksplisit di method controller, contoh: `$this->authorize('view', $employeeBankAccount);` sebelum data ditampilkan ke view. Route harus terproteksi secara eksplisit.
- **Validasi (Inline)**: 
  - Saat men-assign `user_id`, pastikan user tersebut belum di-assign ke employee lain.
  - Setiap perubahan `position_id` pada saat `update()` harus secara otomatis membuat record baru di `employee_position_history` (gunakan `DB::beginTransaction()`).

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php` (Penambahan)**:
  - `view-employees`, `manage-employees` (CRUD pegawai).
  - `view-bank-accounts`, `manage-bank-accounts` (CRUD rekening).
- **`EmployeeSeeder.php`**:
  - Membaca user yang sudah dibuat `UserSeeder`.
  - Membuat dummy Employee (lengkap dengan NIK) dan me-link ke `user_id`.
  - Membuatkan 1 record awal di `employee_position_history` sesuai jabatan awal.
  - Mengisi `employee_bank_accounts` (contoh: Bank BCA, Mandiri) dan komponen gajinya.

## 9. Acceptance Criteria
- [ ] Tabel `employees` dan tabel terkaitnya sukses di-migrate.
- [ ] Proses *Create* atau *Update* posisi pegawai otomatis tersinkronisasi dengan tabel `employee_position_history`.
- [ ] Rekening bank tersimpan dengan validasi `is_primary` (hanya ada 1 primary account per pegawai).
- [ ] Relasi User 1:1 Employee berjalan baik. User baru otomatis dilampirkan atau bisa dipilih saat pembuatan Employee.
- [ ] Gate & Policy membatasi modifikasi data sensitif (rekening) dengan aman tanpa IDOR.

## 10. Testing
- **File Test**: `tests/Feature/EmployeeManagementTest.php`.
- **Test Cases**:
  - Simulasi mutasi jabatan (ubah position via update) => verifikasi database memiliki record history baru dan null `end_date` untuk history sebelumnya diisi sesuai tanggal mutasi.
  - Uji otorisasi `EmployeeBankAccount` dengan route parameter tebakan ID acak (memastikan akses diluar role dikunci dengan 403 Forbidden).

## 11. Risiko & Catatan
- **Konsistensi Data**: Potensi data jabatan di `employees` dan `employee_position_history` tidak sinkron jika proses update gagal di tengah jalan. *Mitigasi*: Wajib menggunakan `DB::beginTransaction()` setiap kali terjadi mutasi jabatan.
- **Keamanan Data Rekening**: Data nomor rekening bersifat sensitif. Hindari menampilkannya secara vulgar di halaman index (gunakan masking seperti `***-***-1234` jika diperlukan), dan pastikan pencegahan IDOR (Insecure Direct Object Reference) melalui pengecekan di Policy.
