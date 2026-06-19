<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    public function index(Request $request)
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

        return view('admin.logs.index', compact('logs', 'categories'));
    }
}