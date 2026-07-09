<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'group', 'key', 'value', 'type', 'description', 'order'
    ];

    /**
     * Retorna o valor no tipo correto e trata criptografia
     */
    public function getTypedValueAttribute()
    {
        return match($this->type) {
            'integer' => (int) $this->value,
            'boolean' => (bool) $this->value,
            'json' => json_decode($this->value, true),
            // 🔥 Se o tipo for password, descriptografa automaticamente
            'password' => $this->decryptPassword($this->value),
            default => $this->value,
        };
    }

    public function scopeOptions($query)
    {
        return $query->where('group', '_system_options');
    }

    public function scopePrefixedOptions($query, $prefix)
    {
        return $query->where('group', '_system_options')
                    ->where('key', 'LIKE', "{$prefix}%");
    }

    public function scopeSettings($query)
    {
        return $query->where('group', '!=', '_system_options');
    }

    /**
     * Busca uma configuração pela chave
     */
    public static function get($key, $default = null)
    {
        $query = static::query();

        // Se tiver notação de ponto, tratamos o primeiro termo como grupo
        if (str_contains($key, '.')) {
            [$group, $k] = explode('.', $key, 2);
            $setting = $query->where('group', $group)->where('key', $k)->first();
        } else {
            // Se não tiver ponto, busca apenas pela chave (padrão antigo)
            $setting = $query->where('key', $key)->first();
        }

        // Retorna o valor decriptado/convertido via acessor typed_value
        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Define uma configuração
     */
    public static function set($key, $value, $group = 'general', $type = 'string')
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
    }

    /**
     * Auxiliar para descriptografar com segurança
     */
    protected function decryptPassword($value)
    {
        if (empty($value)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Se der erro (ex: um valor antigo que mudou de tipo mas não foi encriptado ainda)
            return $value;
        }
    }
}
