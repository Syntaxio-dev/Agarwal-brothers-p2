<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * There is no public sign-up: users are created only from the admin panel,
     * by an administrator, so role / is_active are safe to mass assign here.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Never let the panel lock itself out.
        static::deleting(function (User $user) {
            if (auth()->id() === $user->id) {
                return false;
            }

            if ($user->isAdmin() && ! static::activeAdmins()->whereKeyNot($user->id)->exists()) {
                return false;
            }

            return null;
        });
    }

    public static function roles(): array
    {
        return config('roles', []);
    }

    public static function activeAdmins(): Builder
    {
        return static::query()->where('role', 'admin')->where('is_active', true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }

    public function roleLabel(): string
    {
        return static::roles()[$this->role]['label'] ?? ucfirst((string) $this->role);
    }

    /** Can this user open and change the given admin area (e.g. "products")? */
    public function canManage(string $area): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $access = static::roles()[$this->role]['access'] ?? [];

        if (in_array('*', $access, true)) {
            return true;
        }

        // These areas are administrator-only no matter what a role lists.
        if (in_array($area, ['users', 'site-settings', 'backups'], true)) {
            return false;
        }

        return in_array($area, $access, true);
    }

    /** Only active users with a known role may sign in to the panel. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isStaff();
    }

    /** Active user with a known role: may use the admin panel and preview unpublished pages. */
    public function isStaff(): bool
    {
        return $this->is_active && array_key_exists((string) $this->role, static::roles());
    }
}
