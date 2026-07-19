<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    public function index()
    {
        return view('admin.reference.index');
    }

    /**
     * Lista todos os hooks descobertos no sistema (readonly)
     */
    public function hooks()
    {
        $hooks = [];

        if (class_exists('App\Support\HookDiscoverer')) {
            $discovered = \App\Support\HookDiscoverer::all();

            foreach ($discovered as $hook) {
                $hooks[] = [
                    'name'        => $hook['name'] ?? 'N/A',
                    'type'        => $hook['type'] ?? 'action',
                    'params'      => $hook['params'] ?? '',
                    'description' => $hook['desc'] ?? 'Sem descricao',
                    'file'        => str_replace('\\', '/', $hook['file']),
                ];
            }
        }

        usort($hooks, fn($a, $b) => strcmp($a['name'], $b['name']));

        return view('admin.reference.hooks', compact('hooks'));
    }

    public function permissions()
    {
        return view('admin.reference.roles-permissions');
    }

    public function logs(Request $request)
    {
        $query = AdminLog::query()->with('user');

        // Filtro por Ação (Busca parcial com LIKE)
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        // Filtro por Categoria (Busca exata)
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filtro por Nome do Usuário (Busca parcial com LIKE)
        if ($request->filled('user_name')) {
            $query->where('user_name', 'like', '%' . $request->input('user_name') . '%');
        }

        // Paginação com 30 registros por página ordenando pelos mais recentes
        $logs = $query->latest()->paginate(30);

        // Captura as categorias distintas que existem no banco para alimentar o select do filtro dinamicamente
        $categories = AdminLog::distinct()->pluck('category')->filter();

        return view('admin.reference.logs', compact('logs', 'categories'));
    }

    public function shortcodes()
    {
        $shortcodes = [];

        if (class_exists('App\Helpers\ContentHelper')) {
            $shortcodes = \App\Helpers\ContentHelper::getRegisteredShortcodes();
        }

        return view('admin.reference.shortcodes', compact('shortcodes'));
    }
}
