<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentLockController extends Controller
{
    /**
     * Assume a edição do conteúdo à força (Take Over).
     */
    public function takeOver(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id'   => 'required',
        ]);

        $lock = ContentLockService::takeOver($validated['type'], $validated['id'], auth()->user());

        return response()->json([
            'status'  => 'success',
            'message' => 'Você assumiu a edição deste conteúdo.',
            'lock'    => $lock,
        ]);
    }

    /**
     * Libera o bloqueio ao sair da página ou salvar com sucesso.
     */
    public function release(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id'   => 'required',
        ]);

        $released = ContentLockService::release($validated['type'], $validated['id'], auth()->id());

        return response()->json([
            'status'   => 'success',
            'released' => $released,
        ]);
    }
}
