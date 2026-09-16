<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ContentLockService
{
    /**
     * Tempo de vida do bloqueio em segundos.
     * 60s garante que, se a aba for fechada, o lock expire sozinho rapidamente,
     * enquanto o heartbeat a cada 25s o renova com folga.
     */
    protected static int $ttl = 60;

    /**
     * Retorna a chave de cache padronizada para o conteúdo.
     */
    public static function getLockKey(string $type, int|string $id): string
    {
        return "content_lock:{$type}:{$id}";
    }

    /**
     * Retorna os dados do bloqueio atual ou null se livre.
     */
    public static function getLock(string $type, int|string $id): ?array
    {
        return Cache::get(self::getLockKey($type, $id));
    }

    /**
     * Verifica se o conteúdo está bloqueado por OUTRO usuário.
     */
    public static function isLockedByOther(string $type, int|string $id, int|string $currentUserId): bool
    {
        $lock = self::getLock($type, $id);

        if (!$lock) {
            return false;
        }

        return (int) $lock['user_id'] !== (int) $currentUserId;
    }

    /**
     * Adquire ou renova o lock para um usuário.
     */
    public static function setLock(string $type, int|string $id, User $user): array
    {
        $lockData = [
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'user_email'=> $user->email,
            'locked_at' => now()->toIso8601String(),
        ];

        Cache::put(self::getLockKey($type, $id), $lockData, self::$ttl);

        return $lockData;
    }

    /**
     * Força a tomada de controle (Take Over) por outro usuário.
     */
    public static function takeOver(string $type, int|string $id, User $user): array
    {
        return self::setLock($type, $id, $user);
    }

    /**
     * Libera o bloqueio se o lock pertencer ao usuário atual.
     */
    public static function release(string $type, int|string $id, int|string $currentUserId): bool
    {
        $lock = self::getLock($type, $id);

        if ($lock && (int) $lock['user_id'] === (int) $currentUserId) {
            Cache::forget(self::getLockKey($type, $id));
            return true;
        }

        return false;
    }

    /**
     * Processa a carga do Heartbeat enviada pelo navegador.
     * Este método é acoplado via filtro 'heartbeat_pulse'.
     */
    public static function handleHeartbeat(array $response, array $clientData, User $user): array
    {
        // Se a requisição do heartbeat não veio de uma tela com content lock, ignora
        if (empty($clientData['content_lock'])) {
            return $response;
        }

        $lockRequest = $clientData['content_lock'];
        $type = $lockRequest['type'] ?? null;
        $id = $lockRequest['id'] ?? null;

        if (!$type || !$id) {
            return $response;
        }

        $currentLock = self::getLock($type, $id);

        if (!$currentLock) {
            // Ninguém tem o lock: adquire pela primeira vez
            self::setLock($type, $id, $user);
            $response['content_lock'] = [
                'status' => 'active',
                'message' => 'Bloqueio adquirido com sucesso.'
            ];
        } elseif ((int) $currentLock['user_id'] === (int) $user->id) {
            // O lock já é do usuário atual: renova o TTL
            self::setLock($type, $id, $user);
            $response['content_lock'] = [
                'status' => 'active',
                'message' => 'Bloqueio renovado.'
            ];
        } else {
            // Outro usuário é o dono do lock atual!
            $response['content_lock'] = [
                'status'    => 'locked',
                'locked_by' => $currentLock['user_name'],
                'locked_at' => $currentLock['locked_at'],
                'message'   => "Este conteúdo está sendo editado por {$currentLock['user_name']}."
            ];
        }

        return $response;
    }
}
