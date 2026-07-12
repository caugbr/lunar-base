<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\DashboardWidgetsController;
use App\Support\Dashboard;

class DashboardController extends Controller
{
    public function index()
    {
        // Registra boxes do core
        DashboardWidgetsController::registerBoxes();

        // Coleta todos os boxes registrados
        $allBoxes = collect(Dashboard::getAll())
            ->filter(fn($box) => $this->canViewBox($box))
            ->sortBy('priority')
            ->values();

        // Carrega config do dashboard
        $config = config('admin.dashboard');

        return view('admin.dashboard.index', [
            'boxes'  => $allBoxes,
            'config' => $config,
        ]);
    }

    protected function canViewBox(array $box): bool
    {
        if (empty($box['permission'])) {
            return true;
        }

        return auth()->user()?->permission($box['permission']) ?? false;
    }
}
