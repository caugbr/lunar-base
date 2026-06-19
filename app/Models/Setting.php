<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'group', 'key', 'value', 'type', 'description', 'order'
    ];

    protected $casts = [
        //'value' => 'json',
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

    /**
     * Busca uma configuração pela chave
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
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