<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasTwoFactor;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasTwoFactor;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Verifica se o usuário possui o role informado.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Retorna as permissões do usuário com base no seu role.
     * Admin sempre retorna todas as permissões do sistema.
     */
    public function getPermissionsAttribute(): array
    {
        $config = config('rolesPermissions');

        if ($this->role === 'admin') {
            return array_keys(array_merge(...array_values($config['permissionGroups'] ?? [])));
        }

        return $config['permissionsByRole'][$this->role] ?? [];
    }

    /**
     * Verifica se o usuário possui a permissão informada.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }


    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEditor()
    {
        return $this->role === 'editor';
    }

    public function isAuthor()
    {
        return $this->role === 'author';
    }

    public function isSubscriber()
    {
        return $this->role === 'subscriber';
    }

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
}
