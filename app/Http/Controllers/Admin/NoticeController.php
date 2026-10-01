<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    /**
     * Listagem dos avisos cadastrados
     */
    public function index()
    {
        $notices = AdminNotice::withCount('dismissals')
            ->latest()
            ->paginate(setting('reading.pagination_max_items', 15));

        return view('admin.notices.index', compact('notices'));
    }

    /**
     * Formulário de novo aviso
     */
    public function create()
    {
        return view('admin.notices.create');
    }

    /**
     * Salva um novo aviso no banco
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message'        => 'required|string',
            'type'           => 'required|in:info,warning,error,success',
            'target_type'    => 'required|in:all,roles,users',
            'target_roles'   => 'nullable|array|required_if:target_type,roles',
            'target_users'   => 'nullable|array|required_if:target_type,users',
            'expires_at'     => 'nullable|date',
            'is_active'      => 'nullable|boolean',
        ]);

        // Determina os valores salvos com base na segmentação
        $targetValues = null;
        if ($validated['target_type'] === 'roles') {
            $targetValues = $request->input('target_roles', []);
        } elseif ($validated['target_type'] === 'users') {
            $targetValues = array_map('intval', $request->input('target_users', []));
        }

        $notice = AdminNotice::create([
            'message'       => $validated['message'],
            'type'          => $validated['type'],
            'target_type'   => $validated['target_type'],
            'target_values' => $targetValues,
            'is_active'     => $request->boolean('is_active', true),
            'expires_at'    => $validated['expires_at'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        if (function_exists('logAdmin')) {
            logAdmin("Novo aviso cadastrado (ID #{$notice->id})", "notices");
        }

        return redirect()->route('admin.notices.index')
            ->with('success', 'Aviso publicado com sucesso!');
    }

    /**
     * Formulário de edição do aviso
     */
    public function edit($id)
    {
        $notice = AdminNotice::findOrFail($id);
        return view('admin.notices.edit', compact('notice'));
    }

    /**
     * Atualiza um aviso existente
     */
    public function update(Request $request, $id)
    {
        $notice = AdminNotice::findOrFail($id);

        $validated = $request->validate([
            'message'        => 'required|string',
            'type'           => 'required|in:info,warning,error,success',
            'target_type'    => 'required|in:all,roles,users',
            'target_roles'   => 'nullable|array|required_if:target_type,roles',
            'target_users'   => 'nullable|array|required_if:target_type,users',
            'expires_at'     => 'nullable|date',
            'is_active'      => 'nullable|boolean',
        ]);

        $targetValues = null;
        if ($validated['target_type'] === 'roles') {
            $targetValues = $request->input('target_roles', []);
        } elseif ($validated['target_type'] === 'users') {
            $targetValues = array_map('intval', $request->input('target_users', []));
        }

        $notice->update([
            'message'       => $validated['message'],
            'type'          => $validated['type'],
            'target_type'   => $validated['target_type'],
            'target_values' => $targetValues,
            'is_active'     => $request->boolean('is_active', true),
            'expires_at'    => $validated['expires_at'] ?? null,
        ]);

        if (function_exists('logAdmin')) {
            logAdmin("Aviso atualizado (ID #{$notice->id})", "notices");
        }

        return redirect()->route('admin.notices.index')
            ->with('success', 'Aviso atualizado com sucesso!');
    }

    /**
     * Remove um aviso e seus descartes associados
     */
    public function destroy($id)
    {
        $notice = AdminNotice::findOrFail($id);
        $notice->delete();

        if (function_exists('logAdmin')) {
            logAdmin("Aviso removido (ID #{$id})", "notices");
        }

        return redirect()->route('admin.notices.index')
            ->with('success', 'Aviso removido com sucesso!');
    }

    /**
     * Endpoint chamado via AJAX quando qualquer usuário clica no "X" para fechar
     */
    public function dismiss(Request $request, $id)
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        DB::table('admin_notice_dismissals')->updateOrInsert(
            [
                'notice_id' => $id,
                'user_id'   => $userId,
            ],
            [
                'dismissed_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }
}
