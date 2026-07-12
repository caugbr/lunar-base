<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingsSeeder extends Seeder
{
    /**
     * Popula a tabela settings com os valores default do config.
     *
     * É idempotente: se o valor já existe no banco, não sobrescreve.
     * Isso permite rodar várias vezes sem perder dados do usuário.
     */
    public function run(): void
    {
        // Pega TODAS as definições
        $definitions = getSettingsDefinitions();

        $created = 0;
        $skipped = 0;

        foreach ($definitions as $groupKey => $group) {
            if (!isset($group['fields']) || !is_array($group['fields'])) {
                continue;
            }

            foreach ($group['fields'] as $field) {
                // Pula campos sem key (subtitle, paragraph, etc.)
                if (!isset($field['key'])) {
                    continue;
                }

                $key     = $field['key'];
                $type    = $field['type'] ?? 'text';
                $default = $field['default'] ?? null;

                // Se não tem default, pula (não há nada para popular)
                if ($default === null) {
                    continue;
                }

                // Verifica se já existe no banco (idempotência)
                $exists = Setting::where('group', $groupKey)
                    ->where('key', $key)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Converte o valor default conforme o tipo
                $value = $this->castDefaultValue($default, $type);

                // Insere usando o método set() do model (que já cuida dos casts)
                Setting::set($key, $value, $groupKey, $type);
                $created++;
            }
        }

        $this->command->info(
            "SettingsSeeder: {$created} configurações criadas, {$skipped} já existiam."
        );
    }

    /**
     * Converte o valor default do config para o formato correto do banco,
     * seguindo a mesma lógica do SettingController::update().
     */
    protected function castDefaultValue($value, string $type): string
    {
        return match ($type) {
            // Booleanos: converte para 0/1
            'switch', 'checkbox' => $value ? '1' : '0',

            // Números: garante que seja string numérica
            'number' => (string) $value,

            // Senhas: criptografa (igual o controller faz)
            'password' => Crypt::encryptString((string) $value),

            // Arrays (radio/select com múltiplos): implode com vírgula
            'checkbox_array' => is_array($value) ? implode(',', $value) : (string) $value,

            // Tudo o resto: converte para string
            default => (string) $value,
        };
    }
}
