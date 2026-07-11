<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\SalaryComponent;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get employees to be created
        $users = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['hr-admin', 'finance-admin', 'manager', 'employee']);
        })->get();

        $departments = Department::all();
        $positions = Position::all();
        $salaryComponents = SalaryComponent::all();

        if ($departments->isEmpty() || $positions->isEmpty()) {
            return; // Needs master data
        }

        $nikCounter = 1;
        $bankAccountCounter = 1;

        foreach ($users as $user) {
            // DUMMY DATA FOR DEVELOPMENT/TESTING ONLY - not real NIK/bank account numbers
            $nik = '320000000000' . str_pad($nikCounter++, 4, '0', STR_PAD_LEFT);
            $accountNumber = '00000' . str_pad($bankAccountCounter++, 5, '0', STR_PAD_LEFT);

            $department = $departments->random();
            $position = $positions->random();

            $employee = Employee::create([
                'user_id' => $user->id,
                'nik' => $nik,
                'department_id' => $department->id,
                'position_id' => $position->id,
                'first_name' => explode(' ', $user->name)[0] ?? 'User',
                'last_name' => explode(' ', $user->name)[1] ?? 'Name',
                'email' => $user->email,
                'phone' => '0812' . rand(10000000, 99999999),
                'hire_date' => now()->subMonths(rand(1, 36))->format('Y-m-d'),
                'status' => 'active',
            ]);

            // Create initial position history
            $employee->positionHistories()->create([
                'position_id' => $position->id,
                'start_date' => $employee->hire_date,
                'end_date' => null,
            ]);

            // Create primary bank account
            // DUMMY DATA FOR DEVELOPMENT/TESTING ONLY
            $employee->bankAccounts()->create([
                'bank_name' => collect(['BCA', 'Mandiri', 'BNI', 'BRI'])->random(),
                'account_number' => $accountNumber,
                'account_name' => $user->name,
                'is_primary' => true,
            ]);

            // Attach random salary components
            if ($salaryComponents->isNotEmpty()) {
                $componentsToAttach = $salaryComponents->random(rand(1, 2));
                foreach ($componentsToAttach as $component) {
                    $employee->salaryComponents()->attach($component->id, [
                        'amount' => rand(500000, 2000000)
                    ]);
                }
            }
        }
    }
}
