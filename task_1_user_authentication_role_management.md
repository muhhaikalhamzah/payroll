# DAFTAR TASK IMPLEMENTASI SISTEM PENGGAJIAN (BERDASARKAN PRD)

Berikut adalah daftar seluruh task yang perlu dikerjakan, diurutkan berdasarkan prioritas dan dependensi (dari pondasi hingga pelaporan). Catatan: Task 9 disarankan dieksekusi lebih awal karena sifatnya yang cross-cutting (dipakai oleh modul lain).

1. **`task_1_user_authentication_role_management.md`**
   - **Fitur**: Penyesuaian Role & Auth sesuai PRD (Super Admin, HR Admin, Finance/Payroll Admin, Manager/Approver, Employee)
   - **Dependency**: -
   - **Estimasi Kompleksitas**: Low

2. **`task_9_audit_and_notification.md`** *(Infrastruktur Cross-Cutting)*
   - **Fitur**: Audit Log Immutable & Pusat Notifikasi In-App/Email
   - **Dependency**: Task 1
   - **Estimasi Kompleksitas**: Medium

3. **`task_2_master_data_management.md`**
   - **Fitur**: CRUD Master Data (Jabatan, Departemen, Komponen Gaji, Struktur Gaji, Pemetaan Struktur)
   - **Dependency**: Task 1, Task 9
   - **Estimasi Kompleksitas**: Low

4. **`task_3_employee_data_management.md`**
   - **Fitur**: CRUD Data Pegawai, Rekening Pegawai, Gaji Pokok (Relasi ke User dan Jabatan/Departemen)
   - **Dependency**: Task 2
   - **Estimasi Kompleksitas**: Medium

5. **`task_4_attendance_and_overtime.md`**
   - **Fitur**: Manajemen Kehadiran, Import Absensi, dan Pencatatan Lembur
   - **Dependency**: Task 3
   - **Estimasi Kompleksitas**: Medium

6. **`task_6_payroll_approval_workflow.md`** *(Infrastruktur Approval)*
   - **Fitur**: Alur Persetujuan Berjenjang & Engine Polymorphic
   - **Dependency**: Task 1, Task 9
   - **Estimasi Kompleksitas**: High

7. **`task_8_leave_management.md`**
   - **Fitur**: Manajemen Cuti, Akrual Saldo, Pengajuan & Persetujuan
   - **Dependency**: Task 3, Task 6
   - **Estimasi Kompleksitas**: Medium

8. **`task_10_employee_loans.md`**
   - **Fitur**: Pengajuan Pinjaman Mandiri, Validasi Plafon, & Persetujuan
   - **Dependency**: Task 3, Task 6
   - **Estimasi Kompleksitas**: Medium

9. **`task_5_payroll_calculation_and_generation.md`**
   - **Fitur**: Proses Generate dan Kalkulasi Gaji Otomatis (Gaji Pokok + Tunjangan + Lembur - Potongan Kasbon/Pinjaman)
   - **Dependency**: Task 3, Task 4, Task 6, Task 8, Task 10
   - **Estimasi Kompleksitas**: High

10. **`task_11_thr_management.md`**
    - **Fitur**: Kalkulasi Tunjangan Hari Raya (Proporsional) & PPh 21 Non-Reguler
    - **Dependency**: Task 5
    - **Estimasi Kompleksitas**: High

11. **`task_7_reporting_and_payslip.md`**
    - **Fitur**: Laporan Penggajian, Export PDF/Excel, Generate Slip Gaji & THR
    - **Dependency**: Task 5, Task 11
    - **Estimasi Kompleksitas**: Medium

---

# DETAIL TASK 1

## 1. Deskripsi & Tujuan Task
Melakukan penyesuaian pada sistem autentikasi dan manajemen user existing agar selaras dengan skema Role-Based Access Control (RBAC) pada PRD Sistem Penggajian. Mengganti sistem role existing yang hanya menggunakan kolom enum sederhana, menjadi sistem relasional yang memisahkan tabel role dan permission secara eksplisit, lalu menerapkan otorisasi akses menggunakan Laravel Gate dan Policy.

## 2. Referensi PRD
- **Bab 8: Role-Based Access Control (RBAC) Matrix**
- **Bab 2: Modul 1 (Manajemen User dan Hak Akses)**

## 3. Analisis Gap (Existing vs Target)

| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Arsitektur** | Standard MVC (Fat Controller), validasi inline di Controller | Tetap menggunakan Standard MVC & inline validation (menjaga konsistensi) |
| **Testing** | Pest framework (tests/Feature/ExampleTest.php) | Pest framework, perlu ditambah test untuk user, role & policy check |
| **Tabel Users** | Memiliki field `role` berupa `enum('Superadmin', 'Admin')` | Hapus kolom `role`, tambahkan kolom `role_id` (foreign key ke tabel `roles`) |
| **Manajemen Role & Otorisasi** | Hardcoded ENUM di tabel users, tidak ada tabel permission | Menggunakan tabel terpisah: `roles`, `permissions`, `permission_role`. Otorisasi menggunakan Laravel Gate & Policy. |
| **Validasi Input** | `$request->validate()` inline di `UserController` | Tetap inline di `UserController`, ubah rules role menyesuaikan pengecekan existensi `role_id` di tabel `roles` |

## 4. Strategi Migrasi Data Existing
- Data user lama (existing) **diabaikan** karena project masih dalam tahap development dan belum ada data produksi nyata.
- **Migration `up()`**: Drop kolom `role` lama di tabel `users` dan tambahkan kolom `role_id` (foreign key ke tabel `roles`).
- **Migration `down()`**: Drop kolom `role_id` dan foreign key constraint-nya, lalu tambahkan kembali kolom `role` lama tanpa perlu melakukan pemetaan (mapping) data.

## 5. Perubahan Struktur Database
- **Migration Baru**: `xxxx_xx_xx_xxxxxx_create_roles_and_permissions_tables.php` (berisi skema tabel roles, permissions, dan permission_role).
- **Migration Baru (Update Users)**: `xxxx_xx_xx_xxxxxx_add_role_id_to_users_table.php` (berisi perubahan tabel users).
- **Skema Tabel `roles`**:
  - `id` (bigIncrements)
  - `name` (string, misal: "Super Admin", "HR Admin")
  - `slug` (string, unik, misal: "super-admin", "hr-admin")
  - `description` (text, nullable)
  - `timestamps`
- **Skema Tabel `permissions`**:
  - `id` (bigIncrements)
  - `name` (string, misal: "View Users", "Create User")
  - `slug` (string, unik, misal: "view-users", "create-user")
  - `timestamps`
- **Skema Tabel `permission_role`** (Pivot Table):
  - `role_id` (foreignId refer ke `roles.id`, cascade delete)
  - `permission_id` (foreignId refer ke `permissions.id`, cascade delete)
  - `primary` key pada `(role_id, permission_id)`
- **Perubahan Tabel `users`**:
  - `dropColumn('role')`
  - `foreignId('role_id')->constrained('roles')->onDelete('restrict')`
  - *(Catatan: `onDelete('restrict')` digunakan untuk mencegah role terhapus secara tidak sengaja selama masih dipakai oleh user aktif).*

## 6. Perubahan pada Model
- **Model `Role` (`app/Models/Role.php`)**:
  - Fillable: `['name', 'slug', 'description']`
  - Relasi `permissions()`: `return $this->belongsToMany(Permission::class);`
  - Relasi `users()`: `return $this->hasMany(User::class);`
- **Model `Permission` (`app/Models/Permission.php`)**:
  - Fillable: `['name', 'slug']`
  - Relasi `roles()`: `return $this->belongsToMany(Role::class);`
- **Model `User` (`app/Models/User.php`)**:
  - Hapus `'role'` dari property `#[Fillable]`, tambahkan `'role_id'`.
  - Relasi `role()`: `return $this->belongsTo(Role::class);`
  - Tambahkan helper method yang mengecek relasi role menggunakan null-safe operator PHP 8:
    ```php
    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'super-admin';
    }

    public function isHRAdmin(): bool
    {
        return $this->role?->slug === 'hr-admin';
    }

    // Terapkan pola yang sama untuk isFinanceAdmin, isManager, isEmployee...

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->role?->permissions->contains('slug', $permissionSlug) ?? false;
    }
    ```

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `app/Http/Controllers/UserController.php`
  - Pada form create dan edit, lempar (pass) data dari `Role::all()` ke view.
  - Pada method `store()` dan `update()`, perbarui rules validasi:
    `'role_id' => 'required|exists:roles,id'`
- **Views**: Pada form create dan edit (`resources/views/user/create.blade.php` & `edit.blade.php`), dropdown role mengambil data dinamis dari iterasi variabel `$roles`.
- **Otorisasi (Gate & Policy)**:
  - **AppServiceProvider**: Definisikan Gate dinamis. Pada method `boot()`, ambil semua permission beserta role-nya dari database dan simpan di cache untuk menghindari overhead query berulang.
    ```php
    $permissions = Cache::remember('permissions_with_roles', now()->addHours(24), function () {
        return Permission::with('roles')->get();
    });

    foreach ($permissions as $permission) {
        Gate::define($permission->slug, function (User $user) use ($permission) {
            return $user->role_id ? $permission->roles->contains('id', $user->role_id) : false;
        });
    }
    ```
    *(Catatan penting: Cache ini perlu di-clear menggunakan `Cache::forget('permissions_with_roles')` setiap kali ada perubahan data role/permission, misalnya melalui halaman admin kelola role, agar tidak terjadi data permission yang basi/stale).*
  - **UserPolicy (`app/Policies/UserPolicy.php`)**: Definisikan policy untuk model User (misal method `viewAny`, `create`, `update`, `delete`), dimana setiap method akan mengecek Gate spesifik, atau langsung memanfaatkan nama permission.
  - Terapkan policy di controller dengan menggunakan `$this->authorize('viewAny', User::class);` atau middleware `'can:view-users'` pada routing.

## 8. Seeder untuk Data Dummy
- **File Seeder 1: `RolePermissionSeeder.php`**
  - Mengisi tabel `roles`, `permissions`, dan `permission_role`.
  - Harus dijalankan SEBELUM `UserSeeder`.
  - Mendefinisikan kelima role (Super Admin, HR Admin, Finance Admin, Manager, Employee).
  - Mendefinisikan seluruh permission sesuai RBAC Matrix di PRD Bab 8 (12 modul).
  - Contoh konkret penamaan slug permission mengikuti konvensi `kata-kerja-objek` (kebab-case):
    - `view-users`, `create-user`, `update-user`, `delete-user`
    - `create-payroll-run`, `approve-payroll-run`
    - `submit-leave-request`, `approve-leave-request`
    - `view-payslip-own`, `view-payslip-all`
    *(Konvensi penamaan ini harus konsisten digunakan untuk Task 2 dan seterusnya agar semua permission mengikuti format standar yang sama).*
  - Memasangkan permission ke role masing-masing melalui tabel pivot `permission_role`.
- **File Seeder 2: `UserSeeder.php`**
  - Membuat dummy user dan menghubungkannya ke tabel roles melalui `role_id`.
  - Super Admin (1 user) -> `superadmin@payroll.com`
  - HR Admin (2 user) -> `hr1@payroll.com`, `hr2@payroll.com`
  - Finance Admin (2 user) -> `finance1@payroll.com`, `finance2@payroll.com`
  - Manager (3 user) -> `manager1@payroll.com`, dll.
  - Employee (5 user) -> `employee1@payroll.com`, dll.
  - **Password Default**: `password123` (ditandai `// FOR DEVELOPMENT ONLY`)

## 9. Acceptance Criteria
- [ ] Terdapat tabel `roles`, `permissions`, `permission_role` di database sesuai skema.
- [ ] Kolom `role` lama di tabel `users` terhapus dan digantikan oleh `role_id`.
- [ ] Input dropdown pada form Create dan Edit User mengambil data dinamis dari tabel `roles`.
- [ ] Validasi backend memeriksa input `role_id` valid ke tabel `roles`.
- [ ] `RolePermissionSeeder` berhasil memasukkan data role, permission, dan pivot-nya sesuai RBAC matrix.
- [ ] `UserSeeder` berhasil men-generate minimal 13 user dengan terhubung ke `role_id` masing-masing.
- [ ] Gate & Policy berhasil memblokir (return 403 Forbidden) aksi yang tidak diizinkan sesuai role user.
- [ ] Permission check tidak melakukan query berulang ke database pada setiap request (sudah menggunakan cache sesuai Bagian 7).

## 10. Testing
- **File Test**: Tambahkan/Ubah test di `tests/Feature/UserManagementTest.php` (Pest PHP).
- **Test Cases**:
  - Test struktur relasi model (User belongsTo Role, Role belongsToMany Permission).
  - Test helper function `hasPermission()` di model User berjalan akurat dan null-safe.
  - Test endpoint index hanya bisa diakses role dengan permission `view-users`.
  - Test endpoint create/store berhasil dan memblokir pengguna tanpa permission.
  - Test validasi invalid `role_id`.

## 11. Risiko & Catatan
- **N+1 Query pada Permission Check**: Query untuk mengecek permission secara dinamis (terutama di `AppServiceProvider` atau saat memanggil `$user->hasPermission()`) bisa menambah JOIN tambahan per request. Pastikan logic cache digunakan untuk roles dan permissions, atau eager load (with) saat query, agar tidak terjadi kendala performance N+1 query.
- **Dependency Seeder**: Pastikan `RolePermissionSeeder` dijalankan SEBELUM `UserSeeder` (terutama saat menggunakan `DatabaseSeeder`), agar relasi `role_id` tidak mengalami error foreign key constraint.
