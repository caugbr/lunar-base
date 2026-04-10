<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Relacionamento com as permissões da role
     */
    public function permissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    /**
     * Relacionamento com os usuários que têm esta role
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Verifica se a role tem uma permissão específica
     */
    public function hasPermission($permission): bool
    {
        return $this->permissions()->where('permission', $permission)->exists();
    }

    /**
     * Adiciona uma permissão à role
     */
    public function givePermission($permission): void
    {
        if (!$this->hasPermission($permission)) {
            RolePermission::create([
                'role_id' => $this->id,
                'permission' => $permission,
            ]);
        }
    }

    /**
     * Remove uma permissão da role
     */
    public function revokePermission($permission): void
    {
        $this->permissions()->where('permission', $permission)->delete();
    }

    /**
     * Sincroniza permissões (substitui todas as existentes)
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions()->delete();

        foreach ($permissions as $permission) {
            RolePermission::create([
                'role_id' => $this->id,
                'permission' => $permission,
            ]);
        }
    }

    /**
     * Retorna a lista de permissões como array
     */
    public function getPermissionsListAttribute(): array
    {
        return $this->permissions->pluck('permission')->toArray();
    }
}
