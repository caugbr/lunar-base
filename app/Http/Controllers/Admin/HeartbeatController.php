<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeartbeatController extends Controller
{
    public function pulse(Request $request): JsonResponse
    {
        // O middleware 'auth' já garante que o usuário está logado aqui.
        $user = auth()->user();

        // Dados enviados pelo navegador (agrupados por módulos/chaves)
        $clientData = $request->input('data', []);

        // Resposta base do Core
        $response = [
            'auth_check' => [
                'status'  => true,
                'user_id' => $user->id,
                'name'    => $user->name,
            ],
            'server_time' => now()->toIso8601String(),
        ];

        // Ponto de extensão: Módulos (como ContentLock) injetam suas respostas aqui
        $response = apply_filters('heartbeat_pulse', $response, $clientData, $user);

        return response()->json($response);
    }
}
