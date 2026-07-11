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
        ];

        $permissionModels = [];
        foreach ($permissionsList as $perm) {
            $permissionModels[$perm] = \App\Models\Permission::create([
                'name' => ucwords(str_replace('-', ' ', $perm)),
                'slug' => $perm
            ]);
        }

        // Define Roles
        $superAdmin = \App\Models\Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $hrAdmin = \App\Models\Role::create(['name' => 'HR Admin', 'slug' => 'hr-admin']);
        $financeAdmin = \App\Models\Role::create(['name' => 'Finance Admin', 'slug' => 'finance-admin']);
        $manager = \App\Models\Role::create(['name' => 'Manager', 'slug' => 'manager']);
        $employee = \App\Models\Role::create(['name' => 'Employee', 'slug' => 'employee']);

        // Assign Permissions
        // Super Admin gets all
        $superAdmin->permissions()->attach(array_column($permissionModels, 'id'));

        // HR Admin
        $hrAdmin->permissions()->attach([
            $permissionModels['view-users']->id,
            $permissionModels['create-user']->id,
            $permissionModels['update-user']->id,
            $permissionModels['view-payslip-all']->id,
        ]);

        // Finance Admin
        $financeAdmin->permissions()->attach([
            $permissionModels['create-payroll-run']->id,
            $permissionModels['approve-payroll-run']->id,
            $permissionModels['view-payslip-all']->id,
        ]);

        // Manager
        $manager->permissions()->attach([
            $permissionModels['approve-leave-request']->id,
            $permissionModels['view-payslip-own']->id,
        ]);

        // Employee
        $employee->permissions()->attach([
            $permissionModels['submit-leave-request']->id,
            $permissionModels['view-payslip-own']->id,
        ]);
    }
}
