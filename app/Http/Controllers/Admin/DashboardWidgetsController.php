<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Models\Plugin;
use App\Models\Theme;
use App\Support\Dashboard;

class DashboardWidgetsController extends Controller
{
    public static function registerBoxes()
    {
        Dashboard::add('system-welcome', [
            'title'      => 'Bem-vindo',
            'icon'       => 'moon',
            'controller' => 'App\Http\Controllers\Admin\DashboardWidgetsController@welcome',
            'span'       => 2,
            'priority'   => 1,
        ]);
        Dashboard::add('system-stats', [
            'title'      => 'Estatísticas',
            'icon'       => 'chart-line',
            'controller' => 'App\Http\Controllers\Admin\DashboardWidgetsController@stats',
            'span'       => 2,
            'priority'   => 2,
        ]);
    }

    public function stats()
    {
        $pages = Page::where('status', 'published')->count();
        $posts = Post::where('status', 'published')->count();
        $activePlugins = Plugin::where('is_active', 1)->count();
        $activeTheme = Theme::where('is_active', 1)->value('name') ?? 'nenhum (padrão Lunar Base)';
        $roles = config('rolesPermissions.roles');
        $users = [];
        foreach ($roles as $role => $info) {
            $users[$role] = [
                "name" => $info["name"],
                "count" => User::where('role', $role)->count()
            ];
        }

        return view('admin.dashboard.boxes.stats', compact('pages', 'posts', 'activePlugins', 'activeTheme', 'users'));
    }

    public function welcome()
    {
        return view('admin.dashboard.boxes.welcome');
    }
}
