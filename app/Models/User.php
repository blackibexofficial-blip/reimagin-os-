<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active', 'phone', 'job_title', 'avatar_path',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === Role::Owner;
    }

    public function isAdmin(): bool
    {
        return $this->role->atLeast(Role::Admin);
    }

    public function hasRoleAtLeast(Role $role): bool
    {
        return $this->role->atLeast($role);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(mb_substr($parts[0] ?? '', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }
}
