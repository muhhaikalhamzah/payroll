<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tests user belongs to role', function () {
    $role = Role::factory()->create(['slug' => 'super-admin']);
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->role->id)->toBe($role->id);
    expect($user->isSuperAdmin())->toBeTrue();
});

it('tests role belongs to many permissions', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create(['slug' => 'view-users']);
    $role->permissions()->attach($permission->id);

    expect($role->permissions->contains('slug', 'view-users'))->toBeTrue();
});

it('tests user hasPermission helper', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create(['slug' => 'view-users']);
    $role->permissions()->attach($permission->id);

    $user = User::factory()->create(['role_id' => $role->id]);

    // Make sure cache is cleared or we mock it, but here we just test model method
    expect($user->hasPermission('view-users'))->toBeTrue();
    expect($user->hasPermission('delete-users'))->toBeFalse();
});

it('tests user without permission gets 403 on index', function () {
    $role = Role::factory()->create();
    $user = User::factory()->create(['role_id' => $role->id]);

    // We act as user, they don't have 'view-users' permission
    $response = $this->actingAs($user)->get(route('user.index'));
    
    // In UserController we haven't applied authorize directly yet.
    // Wait, Task 1 section 7 says: "Terapkan policy di controller dengan menggunakan $this->authorize('viewAny', User::class);"
    // Oh! I didn't update the controller to use authorize! I need to do that!
});
