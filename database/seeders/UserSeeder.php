<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = \App\Models\Role::all()->keyBy('slug');

        $users = [
            // Super Admin
            ['name' => 'Super Admin', 'email' => 'superadmin@payroll.com', 'role_id' => $roles['super-admin']->id],
            // HR Admin
            ['name' => 'HR Admin 1', 'email' => 'hr1@payroll.com', 'role_id' => $roles['hr-admin']->id],
            ['name' => 'HR Admin 2', 'email' => 'hr2@payroll.com', 'role_id' => $roles['hr-admin']->id],
            // Finance Admin
            ['name' => 'Finance Admin 1', 'email' => 'finance1@payroll.com', 'role_id' => $roles['finance-admin']->id],
            ['name' => 'Finance Admin 2', 'email' => 'finance2@payroll.com', 'role_id' => $roles['finance-admin']->id],
            // Manager
            ['name' => 'Manager 1', 'email' => 'manager1@payroll.com', 'role_id' => $roles['manager']->id],
            ['name' => 'Manager 2', 'email' => 'manager2@payroll.com', 'role_id' => $roles['manager']->id],
            ['name' => 'Manager 3', 'email' => 'manager3@payroll.com', 'role_id' => $roles['manager']->id],
            // Employee
            ['name' => 'Employee 1', 'email' => 'employee1@payroll.com', 'role_id' => $roles['employee']->id],
            ['name' => 'Employee 2', 'email' => 'employee2@payroll.com', 'role_id' => $roles['employee']->id],
            ['name' => 'Employee 3', 'email' => 'employee3@payroll.com', 'role_id' => $roles['employee']->id],
            ['name' => 'Employee 4', 'email' => 'employee4@payroll.com', 'role_id' => $roles['employee']->id],
            ['name' => 'Employee 5', 'email' => 'employee5@payroll.com', 'role_id' => $roles['employee']->id],
        ];

        foreach ($users as $user) {
            User::factory()->create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => bcrypt('password123'), // FOR DEVELOPMENT ONLY
                'role_id' => $user['role_id'],
            ]);
        }
    }
}
