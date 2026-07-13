<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Cuti Tahunan', 'max_days' => 12, 'is_carry_forward' => false],
            ['name' => 'Cuti Sakit', 'max_days' => 14, 'is_carry_forward' => false],
            ['name' => 'Cuti Melahirkan', 'max_days' => 90, 'is_carry_forward' => false],
        ];

        foreach ($types as $type) {
            \App\Models\LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }

        // Generate leave balances for this year
        \Illuminate\Support\Facades\Artisan::call('leave:generate-balances');
    }
}
