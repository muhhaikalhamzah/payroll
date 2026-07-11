# DETAIL TASK 2

## 1. Deskripsi & Tujuan Task
Membuat fitur CRUD (Create, Read, Update, Delete) untuk Master Data Sistem Penggajian yang meliputi Departemen, Jabatan, Struktur Gaji, dan Komponen Gaji. Entitas master data ini akan menjadi tulang punggung bagi modul manajemen pegawai dan kalkulasi penggajian.

## 2. Referensi PRD
- **Bab 9: Skema Data** (Tabel `departments`, `positions`, `salary_structures`, `salary_components`)
- **Bab 2: Modul 1** (Struktur Organisasi)
- **Bab 2: Modul 2** (Struktur Gaji & Komponen)

## 3. Analisis Gap (Existing vs Target)

| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Ketersediaan Modul** | Modul master data (departemen, jabatan, komponen gaji, dll) belum ada. | Dibuatkan fitur CRUD baru secara terpisah untuk tiap entitas master. |
| **Arsitektur** | Tidak ada controller/model/view terkait. | Mengikuti arsitektur Standard MVC dengan validasi inline di dalam Controller. |
| **Testing** | Belum ada test. | Penambahan testing CRUD Master Data di Pest (Feature test). |

## 4. Strategi Migrasi Data Existing
- Tidak diperlukan migrasi atau mapping data lama karena ini adalah tabel yang benar-benar baru.

## 5. Perubahan Struktur Database
Buat file migration baru mengikuti urutan independensi (departemen dan komponen tidak memiliki foreign key dependen, diikuti oleh jabatan dan struktur gaji):
- `xxxx_xx_xx_xxxxxx_create_departments_table.php`
  - `id`, `name` (string), `description` (text, nullable).
  - `parent_department_id` (foreignId nullable, self-referencing refer ke `departments.id` dengan `onDelete('set null')` atau `cascade`).
  - `timestamps`.
- `xxxx_xx_xx_xxxxxx_create_positions_table.php`
  - `id`, `department_id` (foreignId refer ke `departments.id`, `onDelete('restrict')`).
  - `name` (string).
  - `timestamps`.
- `xxxx_xx_xx_xxxxxx_create_salary_components_table.php`
  - `id`, `name` (string), `type` (enum/string: 'allowance', 'deduction').
  - `is_fixed` (boolean, penanda variabel/tetap).
  - `is_taxable` (boolean).
  - `timestamps`.
- `xxxx_xx_xx_xxxxxx_create_salary_structures_table.php`
  - `id`, `position_id` (foreignId refer ke `positions.id`, `onDelete('restrict')`).
  - `base_salary` (decimal/integer).
  - `timestamps`.

*(Catatan: Self-referencing pada `departments` digunakan untuk memfasilitasi hierarki organisasi. `onDelete('restrict')` pada `position` dan `salary_structure` dipakai untuk mencegah penghapusan jika masih berelasi).*

## 6. Perubahan pada Model
- **Model `Department` (`app/Models/Department.php`)**:
  - Fillable: `['name', 'description', 'parent_department_id']`.
  - Relasi `parent()`: `return $this->belongsTo(Department::class, 'parent_department_id');`
  - Relasi `children()`: `return $this->hasMany(Department::class, 'parent_department_id');`
  - Relasi `positions()`: `return $this->hasMany(Position::class);`
- **Model `Position` (`app/Models/Position.php`)**:
  - Fillable: `['department_id', 'name']`.
  - Relasi `department()`: `return $this->belongsTo(Department::class);`
  - Relasi `salaryStructure()`: `return $this->hasOne(SalaryStructure::class);`
- **Model `SalaryComponent` (`app/Models/SalaryComponent.php`)**:
  - Fillable: `['name', 'type', 'is_fixed', 'is_taxable']`.
- **Model `SalaryStructure` (`app/Models/SalaryStructure.php`)**:
  - Fillable: `['position_id', 'base_salary']`.
  - Relasi `position()`: `return $this->belongsTo(Position::class);`

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: Pembuatan 4 controller baru: `DepartmentController`, `PositionController`, `SalaryComponentController`, `SalaryStructureController`.
- **Validasi (Inline)**: Memastikan constraint bisnis, misal: parent_department_id valid, `type` pada komponen harus 'allowance' atau 'deduction'.
- **Views**: Pembuatan view `index`, `create`, `edit` untuk masing-masing master data di dalam folder `resources/views/master-data/`.
- **Otorisasi (Gate & Policy)**:
  - Pembuatan Policy baru: `DepartmentPolicy`, `PositionPolicy`, `SalaryComponentPolicy`, `SalaryStructurePolicy`.
  - Semua method policy (`viewAny`, `create`, `update`, `delete`) memanggil Gate/Permission secara eksplisit seperti pada Task 1.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php` (Penambahan Permission Baru)**:
  - Sesuai dengan RBAC PRD, fitur ini hanya untuk role **Super Admin** & **HR Admin**. Tambahkan permission berikut ke seeder (kebab-case):
    - `view-departments`, `manage-departments`
    - `view-positions`, `manage-positions`
    - `view-salary-components`, `manage-salary-components`
    - `view-salary-structures`, `manage-salary-structures`
  - Hubungkan permission ini ke role `super-admin` dan `hr-admin`.
- **Seeder Master Data**:
  - `MasterDataSeeder.php` (mengeksekusi seeder turunan).
  - Dummy Department: "Human Resources", "Engineering" (bisa punya child "Backend" & "Frontend").
  - Dummy Position: "HR Manager", "Backend Developer".
  - Dummy Component: "Tunjangan Makan" (allowance, fixed, non-taxable), "Potongan Keterlambatan" (deduction, variable, non-taxable).
  - Dummy Salary Structure: "Backend Developer" -> Base Salary 10.000.000.

## 9. Acceptance Criteria
- [ ] Terdapat 4 tabel master data baru (`departments`, `positions`, `salary_components`, `salary_structures`).
- [ ] Otorisasi menggunakan Policy yang terhubung ke Gate permission.
- [ ] Terdapat tambahan permission (`manage-departments`, dll) pada tabel `permissions`.
- [ ] Role Super Admin & HR Admin bisa melakukan CRUD pada semua master data.
- [ ] Role selain admin ditolak aksesnya (403 Forbidden).
- [ ] Hierarki departemen (parent/child) dapat disimpan dan ditampilkan.

## 10. Testing
- **File Test**: Tambahkan `tests/Feature/MasterDataManagementTest.php`.
- **Test Cases**:
  - Memastikan otorisasi berjalan: HR Admin bisa create department, Manager biasa mendapat 403.
  - CRUD cycle untuk Department, Position, SalaryComponent, SalaryStructure berjalan valid.
  - Uji validasi input (misal mencegah circular dependency jika parent departemen mengarah ke dirinya sendiri - atau minimal tidak throw DB exception).

## 11. Risiko & Catatan
- **Circular Dependency**: Pada relasi self-referencing `departments`, perlu dipastikan di frontend/backend saat update agar sebuah departemen tidak di-set menjadikan dirinya sendiri atau anak dari childnya sebagai parent.
- **Relasi dengan Karyawan**: Jika master data (misal jabatan) sudah dipakai oleh karyawan aktif, penghapusan (`delete()`) harus gagal dengan anggun (graceful fallback berkat `onDelete('restrict')`). Perlu ditangkap exception SQL-nya dan dikembalikan sebagai _friendly error message_.
