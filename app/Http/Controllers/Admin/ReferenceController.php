<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Support\HookDiscoverer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ReferenceController extends Controller
{
    public function index()
    {
        return view('admin.reference.index');
    }

    /**
     * Lista todos os hooks de Template (<x-hook>) descobertos no sistema
     */
    public function hooks()
    {
        $hooks = [];

        if (class_exists(HookDiscoverer::class)) {
            $discovered = HookDiscoverer::templates();

            foreach ($discovered as $hook) {
                $hooks[] = [
                    'name'        => $hook['name'] ?? 'N/A',
                    'type'        => $hook['type'] ?? 'action',
                    'params'      => $hook['params'] ?? '',
                    'description' => $hook['desc'] ?? 'Sem descrição',
                    'file'        => str_replace('\\', '/', $hook['file']),
                    'sector'      => $hook['sector'] ?? 'system',
                ];
            }
        }

        usort($hooks, fn($a, $b) => strcmp($a['name'], $b['name']));

        return view('admin.reference.hooks', compact('hooks'));
    }

    /**
     * Lista todos os hooks de PHP (doAction / applyFilters) descobertos no sistema
     */
    public function phpHooks()
    {
        $hooks = [];

        if (class_exists(HookDiscoverer::class)) {
            $discovered = HookDiscoverer::php();

            foreach ($discovered as $hook) {
                $hooks[] = [
                    'name'        => $hook['name'] ?? 'N/A',
                    'type'        => $hook['type'] ?? 'action',
                    'params'      => $hook['params'] ?? '',
                    'description' => $hook['desc'] ?? 'Sem descrição',
                    'file'        => str_replace('\\', '/', $hook['file']),
                    'sector'      => $hook['sector'] ?? 'system',
                ];
            }
        }

        usort($hooks, fn($a, $b) => strcmp($a['name'], $b['name']));

        return view('admin.reference.php-hooks', compact('hooks'));
    }

    /**
     * Força a re-varredura e compilação do cache dos hooks
     */
    public function syncHooks()
    {
        try {
            Artisan::call('hooks:scan');

            return back()->with('success', 'Varredura concluída! Hooks sincronizados com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Falha ao sincronizar hooks: ' . $e->getMessage());
        }
    }

    public function permissions()
    {
        return view('admin.reference.roles-permissions');
    }

    public function logs(Request $request)
    {
        $query = AdminLog::query()->with('user');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('user_name')) {
            $query->where('user_name', 'like', '%' . $request->input('user_name') . '%');
        }

        $logs = $query->latest()->paginate(30);
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
