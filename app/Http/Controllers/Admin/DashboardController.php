<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Dashboard;

class DashboardController extends Controller
{
    public function index()
    {
        // Registra boxes do core (se houver)
        $this->registerSystemBoxes();

        // Coleta todos os boxes registrados
        $allBoxes = collect(Dashboard::getAll())
            ->filter(fn($box) => $this->canViewBox($box))
            ->sortBy('priority')
            ->values();

        // Carrega config do dashboard
        $config = config('dashboard');

        return view('admin.dashboard.index', [
            'boxes'  => $allBoxes,
            'config' => $config,
        ]);
    }

    /**
     * Registra boxes nativos do sistema (core).
     * Pode ser movido para um ServiceProvider no futuro.
     */
    protected function registerSystemBoxes(): void
    {
        Dashboard::add('system-welcome', [
            'title'      => 'Bem-vindo',
            'icon'       => 'moon',
            'controller' => 'App\Http\Controllers\Admin\DashboardController@welcome',
            'span'       => 2,
            'priority'   => 1,
        ]);
    }

    protected function canViewBox(array $box): bool
    {
        if (empty($box['permission'])) {
            return true;
        }

        return auth()->user()?->permission($box['permission']) ?? false;
    }

    public function welcome()
    {
        return view('admin.dashboard.boxes.welcome');
    }
}
