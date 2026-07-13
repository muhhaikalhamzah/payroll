<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'avatar', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'super-admin';
    }

    public function isHRAdmin(): bool
    {
        return $this->role?->slug === 'hr-admin';
    }

    public function isFinanceAdmin(): bool
    {
        return $this->role?->slug === 'finance-admin';
    }

    public function isManager(): bool
    {
        return $this->role?->slug === 'manager';
    }

    public function isEmployee(): bool
    {
        return $this->role?->slug === 'employee';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role?->slug, $roles);
        }
        return $this->role?->slug === $roles;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->role?->permissions->contains('slug', $permissionSlug) ?? false;
    }
}
