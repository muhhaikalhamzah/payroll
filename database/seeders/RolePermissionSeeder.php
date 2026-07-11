<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Permissions
        $permissionsList = [
            'view-users', 'create-user', 'update-user', 'delete-user',
            'create-payroll-run', 'approve-payroll-run',
            'submit-leave-request', 'approve-leave-request',
            'view-payslip-own', 'view-payslip-all',
            'view-departments', 'manage-departments',
            'view-positions', 'manage-positions',
            'view-salary-components', 'manage-salary-components',
            'view-salary-structures', 'manage-salary-structures',
            'view-employees', 'manage-employees',
            'view-bank-accounts', 'manage-bank-accounts',
            'view-attendances', 'manage-attendances', 'import-attendances',
            'view-overtime-requests', 'submit-overtime-requests', 'approve-overtime-requests', 'manage-overtime-requests',
        ];

        $permissionModels = [];
        foreach ($permissionsList as $perm) {
            $permissionModels[$perm] = \App\Models\Permission::firstOrCreate([
                'slug' => $perm
            ], [
                'name' => ucwords(str_replace('-', ' ', $perm))
            ]);
        }

        // Define Roles
        $superAdmin = \App\Models\Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $hrAdmin = \App\Models\Role::firstOrCreate(['slug' => 'hr-admin'], ['name' => 'HR Admin']);
        $financeAdmin = \App\Models\Role::firstOrCreate(['slug' => 'finance-admin'], ['name' => 'Finance Admin']);
        $manager = \App\Models\Role::firstOrCreate(['slug' => 'manager'], ['name' => 'Manager']);
        $employee = \App\Models\Role::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee']);

        // Assign Permissions
        // Super Admin gets all
        $superAdmin->permissions()->syncWithoutDetaching(array_column($permissionModels, 'id'));

        // HR Admin
        $hrAdmin->permissions()->syncWithoutDetaching([
            $permissionModels['view-users']->id,
            $permissionModels['create-user']->id,
            $permissionModels['update-user']->id,
            $permissionModels['view-payslip-all']->id,
            $permissionModels['view-departments']->id,
            $permissionModels['manage-departments']->id,
            $permissionModels['view-positions']->id,
            $permissionModels['manage-positions']->id,
            $permissionModels['view-salary-components']->id,
            $permissionModels['manage-salary-components']->id,
            $permissionModels['view-salary-structures']->id,
            $permissionModels['manage-salary-structures']->id,
            $permissionModels['view-employees']->id,
            $permissionModels['manage-employees']->id,
            $permissionModels['view-bank-accounts']->id,
            $permissionModels['manage-bank-accounts']->id,
            $permissionModels['view-attendances']->id,
            $permissionModels['manage-attendances']->id,
            $permissionModels['import-attendances']->id,
            $permissionModels['view-overtime-requests']->id,
            $permissionModels['manage-overtime-requests']->id,
        ]);

        // Finance Admin
        $financeAdmin->permissions()->syncWithoutDetaching([
            $permissionModels['create-payroll-run']->id,
            $permissionModels['approve-payroll-run']->id,
            $permissionModels['view-payslip-all']->id,
        ]);

        // Manager
        $manager->permissions()->syncWithoutDetaching([
            $permissionModels['approve-leave-request']->id,
            $permissionModels['view-payslip-own']->id,
            $permissionModels['view-attendances']->id,
            $permissionModels['view-overtime-requests']->id,
            $permissionModels['approve-overtime-requests']->id,
        ]);

        // Employee
        $employee->permissions()->syncWithoutDetaching([
            $permissionModels['submit-leave-request']->id,
            $permissionModels['view-payslip-own']->id,
            $permissionModels['view-attendances']->id,
            $permissionModels['view-overtime-requests']->id,
            $permissionModels['submit-overtime-requests']->id,
        ]);
    }
}
