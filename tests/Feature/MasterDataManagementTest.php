<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Position;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Need to seed roles and permissions since RBAC is db-driven
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    $this->superadminRole = Role::where('slug', 'super-admin')->first();
    $this->superadminUser = User::factory()->create(['role_id' => $this->superadminRole->id]);
    
    // An ordinary user without master data permissions
    $this->ordinaryUser = User::factory()->create(['role_id' => null]);
});

// DEPARTMENT TESTS

test('superadmin can view departments', function () {
    Department::factory()->count(3)->create();
    
    $response = $this->actingAs($this->superadminUser)
        ->get(route('departments.index'));
        
    $response->assertStatus(200);
    $response->assertViewHas('departments');
});

test('ordinary user cannot view departments', function () {
    $response = $this->actingAs($this->ordinaryUser)
        ->get(route('departments.index'));
        
    $response->assertStatus(403);
});

test('superadmin can create department', function () {
    $response = $this->actingAs($this->superadminUser)
        ->post(route('departments.store'), [
            'name' => 'HR Department',
            'description' => 'Human Resources'
        ]);
        
    $response->assertRedirect(route('departments.index'));
    $this->assertDatabaseHas('departments', ['name' => 'HR Department']);
});

test('superadmin can delete department', function () {
    $dept = Department::factory()->create();
    
    $response = $this->actingAs($this->superadminUser)
        ->delete(route('departments.destroy', $dept));
        
    $response->assertRedirect(route('departments.index'));
    $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
});

// POSITION TESTS

test('superadmin can create position', function () {
    $dept = Department::factory()->create();
    
    $response = $this->actingAs($this->superadminUser)
        ->post(route('positions.store'), [
            'name' => 'Manager',
            'department_id' => $dept->id
        ]);
        
    $response->assertRedirect(route('positions.index'));
    $this->assertDatabaseHas('positions', ['name' => 'Manager', 'department_id' => $dept->id]);
});

test('cannot delete department if it has positions', function () {
    $dept = Department::factory()->create();
    Position::factory()->create(['department_id' => $dept->id]);
    
    $response = $this->actingAs($this->superadminUser)
        ->delete(route('departments.destroy', $dept));
        
    // Should be redirected back with error
    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('departments', ['id' => $dept->id]);
});

// SALARY COMPONENTS TESTS

test('superadmin can create salary component', function () {
    $response = $this->actingAs($this->superadminUser)
        ->post(route('salary-components.store'), [
            'name' => 'Transport Allowance',
            'type' => 'allowance',
            'is_fixed' => '1'
        ]);
        
    $response->assertRedirect(route('salary-components.index'));
    $this->assertDatabaseHas('salary_components', [
        'name' => 'Transport Allowance',
        'type' => 'allowance',
        'is_fixed' => true,
        'is_taxable' => false
    ]);
});

// SALARY STRUCTURES TESTS

test('superadmin can create salary structure', function () {
    $position = Position::factory()->create();
    
    $response = $this->actingAs($this->superadminUser)
        ->post(route('salary-structures.store'), [
            'position_id' => $position->id,
            'base_salary' => 5000000
        ]);
        
    $response->assertRedirect(route('salary-structures.index'));
    $this->assertDatabaseHas('salary_structures', [
        'position_id' => $position->id,
        'base_salary' => 5000000
    ]);
});
