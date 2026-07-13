<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxTerRate;

class PPh21TerRatesSeeder extends Seeder
{
    public function run(): void
    {
        $data = include 'C:\Users\Lenovo\Downloads\pph21_ter_data.php';

        TaxTerRate::truncate();

        // TER A
        foreach ($data['ter_a'] as $row) {
            TaxTerRate::create([
                'kategori' => 'A',
                'no_lapisan' => $row['no'],
                'batas_bawah' => $row['batas_bawah'],
                'batas_atas' => $row['batas_atas'],
                'tarif' => $row['tarif'],
            ]);
        }

        // TER B
        foreach ($data['ter_b'] as $row) {
            TaxTerRate::create([
                'kategori' => 'B',
                'no_lapisan' => $row['no'],
                'batas_bawah' => $row['batas_bawah'],
                'batas_atas' => $row['batas_atas'],
                'tarif' => $row['tarif'],
            ]);
        }

        // TER C
        foreach ($data['ter_c'] as $row) {
            TaxTerRate::create([
                'kategori' => 'C',
                'no_lapisan' => $row['no'],
                'batas_bawah' => $row['batas_bawah'],
                'batas_atas' => $row['batas_atas'],
                'tarif' => $row['tarif'],
            ]);
        }

        // PASAL 17
        $no = 1;
        foreach ($data['tarif_pasal_17'] as $row) {
            TaxTerRate::create([
                'kategori' => 'PASAL17',
                'no_lapisan' => $no++,
                'batas_bawah' => $row['batas_bawah'],
                'batas_atas' => $row['batas_atas'],
                'tarif' => $row['tarif'],
            ]);
        }
    }
}
