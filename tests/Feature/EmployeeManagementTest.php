<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Run seeders for dependencies
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'MasterDataSeeder']);
    
    // Create HR Admin user
    $hrRole = Role::where('slug', 'hr-admin')->first();
    $this->hrUser = User::factory()->create(['role_id' => $hrRole->id]);
    
    $this->department = Department::first();
    $this->position = Position::first();
    
    \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
        return $user->hasPermission($ability) ?: null;
    });
});

it('can view employees list', function () {
    $this->actingAs($this->hrUser)
        ->get(route('employees.index'))
        ->assertStatus(200);
});

it('can create a new employee with a linked existing user', function () {
    $existingUser = User::factory()->create();

    $payload = [
        'nik' => '3211223344556677',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '08123456789',
        'hire_date' => now()->format('Y-m-d'),
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'status' => 'active',
        'user_action' => 'link_existing',
        'existing_user_id' => $existingUser->id,
    ];

    $response = $this->actingAs($this->hrUser)
        ->post(route('employees.store'), $payload);

    $response->assertRedirect(route('employees.index'));
    
    $this->assertDatabaseHas('employees', [
        'nik' => '3211223344556677',
        'user_id' => $existingUser->id,
        'first_name' => 'John',
    ]);

    $employee = Employee::where('nik', '3211223344556677')->first();
    
    // Check Position History created
    $this->assertDatabaseHas('employee_position_histories', [
        'employee_id' => $employee->id,
        'position_id' => $this->position->id,
    ]);

    // Check Audit Log created
    $this->assertDatabaseHas('audit_logs', [
        'auditable_type' => Employee::class,
        'auditable_id' => $employee->id,
        'action' => 'CREATED',
    ]);
});

it('can create a new bank account for employee', function () {
    $employee = Employee::create([
        'nik' => '1111222233334444',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'hire_date' => now()->format('Y-m-d'),
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'status' => 'active',
    ]);

    $payload = [
        'bank_name' => 'BCA',
        'account_name' => 'Jane Doe',
        'account_number' => '1234567890',
        'is_primary' => true,
    ];

    $this->actingAs($this->hrUser)
        ->post(route('employees.bank-accounts.store', $employee), $payload)
        ->assertRedirect();
        
    $this->assertDatabaseCount('employee_bank_accounts', 1);
    
    $bankAccount = $employee->bankAccounts()->first();
    expect($bankAccount->bank_name)->toBe('BCA');
    expect($bankAccount->account_number)->toBe('1234567890'); // Auto decrypted by model cast
});
