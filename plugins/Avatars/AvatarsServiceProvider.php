<?php

namespace Plugins\Avatars;

use Illuminate\Support\ServiceProvider;
use App\Support\HookManager;
use Plugins\Avatars\Helpers\AvatarHelper;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AvatarsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $routesFile = __DIR__ . '/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }

    public function boot(): void
    {
        // Carrega as views do plugin
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'avatars');

        // Hook 1: Substitui o ícone do header pela foto circular
        HookManager::register('admin.header_user_avatar', function($params) {
            $user = $params['user'] ?? null;
            if (!$user) return '';

            $url = AvatarHelper::getUrl($user);

            return '<img src="' . $url . '" alt="' . e($user->name) . '" class="lucid-icon" style="border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 6px; border: 1px solid rgba(255,255,255,0.15); width: 30px; height: 30px;" />';
        }, 'Avatars Plugin');

        // Hook 2: Injeta a caixa de upload logo abaixo do formulário de perfil
        HookManager::register('admin.profile_after_card', function($params) {
            $user = $params['user'] ?? null;
            if (!$user) return '';

            return view('avatars::profile-upload', ['user' => $user])->render();
        }, 'Avatars Plugin');

        // Escuta o evento de exclusão do Usuário para limpar a imagem física em disco
        User::deleting(function (User $user) {
            $fileName = "avatars/{$user->id}.webp";

            if (Storage::disk('public')->exists($fileName)) {
                Storage::disk('public')->delete($fileName);
            }
        });
    }
}
