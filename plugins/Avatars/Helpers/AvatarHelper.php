<?php

namespace Plugins\Avatars\Helpers;

use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AvatarHelper
{
    /**
     * Retorna a URL do avatar do usuário ou o fallback do Gravatar
     */
    // public static function getUrl(User $user): string
    // {
    //     $fileName = "avatars/{$user->id}.webp";

    //     // 1. Checa a existência física do arquivo (Zero Banco de dados)
    //     if (Storage::disk('public')->exists($fileName)) {
    //         return Storage::disk('public')->url($fileName);
    //     }

    //     // 2. Fallback: Retorna o Gravatar do e-mail do usuário
    //     $hash = md5(strtolower(trim($user->email)));
    //     return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=150";
    // }
    /**
     * Retorna a URL do avatar (Aceita o Modelo de Usuário ou uma string de E-mail)
     */
    public static function getUrl($userOrEmail): string
    {
        $email = '';

        if ($userOrEmail instanceof User) {
            $fileName = "avatars/{$userOrEmail->id}.webp";

            if (Storage::disk('public')->exists($fileName)) {
                return Storage::disk('public')->url($fileName);
            }
            $email = $userOrEmail->email;
        } elseif (is_string($userOrEmail)) {
            $email = $userOrEmail;
        }

        // Retorna o Gravatar baseado no e-mail (resolvendo usuários sem foto ou visitantes)
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=150";
    }
}
