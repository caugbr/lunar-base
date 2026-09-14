<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AddonDependencyService;

class CheckAddonDependencies
{
    protected AddonDependencyService $dependencyService;

    public function __construct(AddonDependencyService $dependencyService)
    {
        $this->dependencyService = $dependencyService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Executa apenas em requisições GET normais e autenticadas do painel admin
        if ($request->isMethod('GET') && !$request->ajax() && auth()->check()) {
            $unresolved = $this->dependencyService->getUnresolvedDependencies();

            if (!empty($unresolved)) {
                // Monta a mensagem para a primeira dependência pendente encontrada
                $dep = $unresolved[0];
                $messageHtml = $this->formatAlertMessage($dep);

                // Injeta no flash de sessão para o <x-admin-alert /> capturar
                if ($messageHtml && !session()->has('warning') && !session()->has('error')) {
                    session()->now('warning', $messageHtml);
                }
            }
        }

        return $next($request);
    }

    /**
     * Formata a mensagem HTML com o link de ação direta
     */
    protected function formatAlertMessage(array $dep): string
    {
        $addonName = htmlspecialchars($dep['name']);
        $source = htmlspecialchars($dep['source_name']);
        $reasonText = $dep['reason'] ? " ({$dep['reason']})" : '';

        // Cenário 1: O plugin já está baixado no disco, apenas desligado
        if ($dep['status'] === 'inactive' && !empty($dep['db_id'])) {
            $toggleUrl = route('admin.plugins.toggle', $dep['db_id']);
            $csrf = csrf_token();

            return "O {$dep['source_type']} <strong>{$source}</strong> requer o plugin <strong>{$addonName}</strong>{$reasonText}. " .
                "<form method='POST' action='{$toggleUrl}' style='display:inline; margin-left: 8px;'>" .
                "<input type='hidden' name='_token' value='{$csrf}'>" .
                "<button type='submit' class='admin-btn admin-btn-sm admin-btn-primary'>Ativar plugin agora</button>" .
                "</form>";
        }

        // Cenário 2: O plugin está faltando no disco e temos a URL de download
        if ($dep['status'] === 'missing' && !empty($dep['download_url'])) {
            $installUrl = route('admin.addons.install_dependency');
            $csrf = csrf_token();

            return "O {$dep['source_type']} <strong>{$source}</strong> requer o plugin <strong>{$addonName}</strong>{$reasonText}. " .
                "<form method='POST' action='{$installUrl}' style='display:inline; margin-left: 8px;'>" .
                "<input type='hidden' name='_token' value='{$csrf}'>" .
                "<input type='hidden' name='name' value='{$dep['name']}'>" .
                "<input type='hidden' name='download_url' value='{$dep['download_url']}'>" .
                "<input type='hidden' name='type' value='{$dep['type']}'>" .
                "<button type='submit' class='admin-btn admin-btn-sm admin-btn-primary'>Baixar e preparar plugin</button>" .
                "</form>";
        }

        // Cenário 3: O plugin falta no disco e não tem URL (aviso manual)
        return "O {$dep['source_type']} <strong>{$source}</strong> requer o plugin <strong>{$addonName}</strong>{$reasonText}. Instale-o para garantir o funcionamento correto.";
    }
}
