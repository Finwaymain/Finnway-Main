<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is Super Admin or full Admin.
     */
    public function isAdmin(): bool
    {
        $email = strtolower(trim($this->email ?? ''));

        // Main Super Admin explicit emails
        if (in_array($email, ['admin@cabme.com', 'admin@fooddelivery.com'])) {
            return true;
        }

        // Sub Admin check MUST return false
        if ($this->role === 'sub_admin' || str_contains($email, 'subadmin')) {
            return false;
        }

        // Only return true if explicitly marked admin/super_admin or role is empty on legacy admin user
        return in_array($this->role, ['admin', 'super_admin']) || empty($this->role);
    }

    /**
     * Check if user is Sub Admin.
     */
    public function isSubAdmin(): bool
    {
        $email = strtolower(trim($this->email ?? ''));
        if (in_array($email, ['admin@cabme.com', 'admin@fooddelivery.com'])) {
            return false;
        }

        return $this->role === 'sub_admin' || str_contains($email, 'subadmin');
    }

    /**
     * Check if user has a specific sidebar / route permission.
     */
    public function hasPermission(string $permissionKey): bool
    {
        // Super Admins have 100% access
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isSubAdmin()) {
            return false;
        }

        $perms = $this->permissions;
        if (is_string($perms)) {
            $perms = json_decode($perms, true) ?? [];
        }

        if (!is_array($perms)) {
            return false;
        }

        return in_array($permissionKey, $perms);
    }
}
