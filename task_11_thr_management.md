# DETAIL TASK 11

## 1. Deskripsi & Tujuan Task
Mengembangkan fungsionalitas pemrosesan dan pendistribusian Tunjangan Hari Raya (THR) secara massal. Modul ini berfokus pada eksekusi siklus *payroll run* bertipe khusus (`THR`) yang terpisah dari siklus gaji bulanan reguler, dengan kalkulasi proporsional menurut durasi masa kerja karyawan (sesuai PP 36/2021), serta menggunakan algoritma pemotongan Pajak PPh 21 khusus Penghasilan Tidak Teratur.

## 2. Referensi PRD
- **Bab 5: Modul 10** (Tunjangan Hari Raya - THR)
- **Bab 6: Business Rules** (Proporsional THR dan PPh 21 Non-Reguler)
- **Bab 9: Skema Data** (Penambahan kolom `type` pada `payroll_runs`)

## 3. Analisis Gap (Existing vs Target)
| Komponen | Kondisi Saat Ini (Existing) | Target (Sesuai PRD) |
| --- | --- | --- |
| **Siklus Payroll** | Baru mencakup 1 tipe bulanan reguler. | Pemrosesan duplikat dengan `type`='THR', kalender tahunan yang independen. |
| **Logic Pajak (PPh 21)** | Metode TER Bulanan. | Metode perhitungan setahunan atas selisih Gaji Reguler vs Gaji Reguler+THR. |
| **Arsitektur** | - | Integrasi *polymorphic approvable* yang sama seperti gaji reguler. |

## 4. Strategi Migrasi Data Existing
- Tidak ada. Sistem memanfaatkan tabel `payroll_runs` dan `payslips` dari Task 5.

## 5. Perubahan Struktur Database
- **Tabel `payroll_runs`** (Modifikasi Ringan dari rancangan Task 5):
  - Memastikan keberadaan kolom `type` berjenis `enum('REGULAR', 'THR')` dengan default `REGULAR`.
- Tabel `payslips` tidak berubah, namun detail dari tabel `payslip_components` untuk tipe THR hanya akan diisi oleh Gaji Pokok (atau ditambah Tunjangan Tetap), tanpa kehadiran BPJS Kesehatan/Ketenagakerjaan dan cicilan pinjaman (terkecuali ada diskresi).

## 6. Perubahan pada Model
- Tidak ada modifikasi relasional pada model. Pemanfaatan entitas akan berfokus di *Service Layer* (misal: `ThrCalculatorService`).

## 7. Perubahan pada CRUD (Create, Read, Update, Delete)
- **Controller**: `ThrRunController` (bisa digabung/extends dari `PayrollRunController` dengan endpoint `/thr-runs`).
- **Kalkulasi Nilai Dasar THR**:
  - Jika **Masa Kerja $\ge$ 12 bulan**: Nominal murni sebesar 1x Gaji Pokok + Tunjangan Tetap.
  - Jika **Masa Kerja < 12 bulan**: Menggunakan hitungan prorata `(Bulan Kerja / 12) * (Gaji Pokok + Tunjangan Tetap)`. Minimal masa kerja berhak THR adalah 1 bulan berkelanjutan.
- **Kalkulasi Pajak PPh 21 Penghasilan Tidak Teratur (Non-Reguler)**:
  - PPh 21 THR **tidak dapat** dihitung menggunakan skema TER Bulanan.
  - Langkah 1: Sistem memproyeksikan/menyetahunkan penghasilan reguler karyawan saat ini, lalu menghitung pajak setahunan (berdasarkan lapisan tarif progresif Pasal 17). Sebut saja sebagai **Pajak A**.
  - Langkah 2: Sistem menyetahunkan penghasilan reguler lalu MENGGABUNGKAN nilai THR, kemudian menghitung pajak setahunannya kembali. Sebut saja sebagai **Pajak B**.
  - Langkah 3: **Pajak B - Pajak A = PPh 21 atas THR**. 
- **Approval Workflow & Distribusi Slip**:
  - DRAFT -> PENDING_FINANCE -> APPROVED -> PAID (Menggunakan trait `Approvable`).
  - Saat `PAID`, sistem men-generate notifikasi In-App/Email ke karyawan bahwa Slip THR dapat diakses. Format PDF slip dibedakan tampilannya dengan slip gaji reguler.

## 8. Seeder untuk Data Dummy
- **`RolePermissionSeeder.php`**:
  - `view-thr-runs`, `create-thr-runs`, `submit-thr-runs`, `approve-thr-runs`, `reject-thr-runs`, `mark-thr-runs-paid`. (RBAC Finance/HR).
- **`ThrSeeder.php`**:
  - Meng-generate 1 siklus simulasi THR secara historis (misal: THR Idulfitri tahun lalu) bagi karyawan dengan berbagai variasi masa kerja agar bisa divalidasi visualnya di UI dan Dashboard Analitik.

## 9. Acceptance Criteria
- [ ] Pegawai bermasa kerja >1 tahun mendapat 100% THR, sedangkan yang <1 tahun mendapat nilai parsial proporsional yang presisi.
- [ ] Komponen potongan pada payslip THR HANYA berupa PPh 21 Non-Reguler (tanpa ada potongan BPJS atau pinjaman, sesuai standar).
- [ ] Logika selisih Pajak B dan Pajak A untuk PPh 21 menghasilkan angka sesuai simulasi kalkulator DJP.
- [ ] File PDF Slip THR berhasil diunduh via sistem *self-service*.

## 10. Testing
- **Test Cases**:
  - Uji *Proration*: Mock `hire_date` karyawan berumur 5 bulan. THR = (5/12) x Gaji, verifikasi ekspektasi hasilnya.
  - Uji Pajak Non-Reguler: Memastikan metode TER tidak dipanggil ketika *flag* enum `type`='THR'.

## 11. Risiko & Catatan
- **Kompleksitas PPh 21**: Hitungan proyeksi disetahunkan (Pajak B vs Pajak A) seringkali membingungkan *user* jika sistem tidak memberikan penjelasan visual (Summary Box) tentang dari mana angka potongan pajak THR tersebut berasal.
  *Mitigasi*: Pada tampilan rincian approval THR bagi Admin Finance, tampilkan *tooltip* rincian nilai Pajak B dan Pajak A agar Finance bisa memeriksa (*crosscheck*) dan meyakini hasil kalkulasi sistem sebelum disetujui (Approve).
