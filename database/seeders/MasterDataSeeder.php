<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hr = \App\Models\Department::create(['name' => 'Human Resources']);
        $engineering = \App\Models\Department::create(['name' => 'Engineering']);
        
        $backend = \App\Models\Department::create(['name' => 'Backend', 'parent_department_id' => $engineering->id]);
        $frontend = \App\Models\Department::create(['name' => 'Frontend', 'parent_department_id' => $engineering->id]);

        $hrManager = \App\Models\Position::create(['department_id' => $hr->id, 'name' => 'HR Manager']);
        $backendDev = \App\Models\Position::create(['department_id' => $backend->id, 'name' => 'Backend Developer']);

        \App\Models\SalaryComponent::create([
            'name' => 'Tunjangan Makan',
            'type' => 'allowance',
            'is_fixed' => true,
            'is_taxable' => false,
        ]);

        \App\Models\SalaryComponent::create([
            'name' => 'Potongan Keterlambatan',
            'type' => 'deduction',
            'is_fixed' => false,
            'is_taxable' => false,
        ]);

        \App\Models\SalaryStructure::create([
            'position_id' => $backendDev->id,
            'base_salary' => 10000000,
        ]);
        
        \App\Models\SalaryStructure::create([
            'position_id' => $hrManager->id,
            'base_salary' => 12000000,
        ]);
    }
}
