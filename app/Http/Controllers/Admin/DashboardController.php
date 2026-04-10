<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Page;

class DashboardController extends Controller
{
    public function index()
    {
        // Totais para os boxes
        $totalUsers = User::count();
        $totalAdmins = User::whereHas('role', function($q) {
            $q->where('slug', 'admin');
        })->count();

        $totalEditors = User::whereHas('role', function($q) {
            $q->where('slug', 'editor');
        })->count();

        $totalViewers = User::whereHas('role', function($q) {
            $q->where('slug', 'viewer');
        })->count();

        $totalPages = Page::count();
        $publishedPages = Page::where('status', 'published')->count();
        $draftPages = Page::where('status', 'draft')->count();

        // Últimas páginas criadas
        $recentPages = Page::with('author')
            ->latest()
            ->limit(5)
            ->get();

        // Últimos usuários criados
        $recentUsers = User::with('role')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAdmins',
            'totalEditors',
            'totalViewers',
            'totalPages',
            'publishedPages',
            'draftPages',
            'recentPages',
            'recentUsers'
        ));
    }
}
