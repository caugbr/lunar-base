<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PartnerWidget;

class AuthenticatePartner
{
    /**
     * Valida o token e retorna o partnerWidget ou uma resposta de erro
     */
    public function validateToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return [
                'error' => true,
                'response' => response()->json([
                    'error' => 'Token não fornecido',
                    'message' => 'Envie o token no header Authorization: Bearer {token}'
                ], 401)
            ];
        }

        $partnerWidget = PartnerWidget::where('api_key', $token)
            ->where('status', 'active')
            ->with(['partner', 'widget'])
            ->first();

        if (!$partnerWidget) {
            return [
                'error' => true,
                'response' => response()->json([
                    'error' => 'Token inválido ou inativo'
                ], 401)
            ];
        }

        return [
            'error' => false,
            'partnerWidget' => $partnerWidget
        ];
    }

    /**
     * Valida o domínio da requisição
     */
    public function validateDomain(Request $request, $partnerWidget)
    {
        $referer = $request->headers->get('referer');

        if (!$referer) {
            \Log::info('Requisição sem referer', [
                'token' => $request->bearerToken(),
                'ip' => $request->ip()
            ]);
            return ['error' => false]; // sem referer, permite (ou pode bloquear se quiser)
        }

        $domain = parse_url($referer, PHP_URL_HOST);

        $allowedDomains = [];

        if ($partnerWidget->allowed_domains) {
            $allowedDomains = is_string($partnerWidget->allowed_domains)
                ? json_decode($partnerWidget->allowed_domains, true)
                : $partnerWidget->allowed_domains;
        } elseif ($partnerWidget->partner->allowed_domains) {
            $allowedDomains = is_string($partnerWidget->partner->allowed_domains)
                ? json_decode($partnerWidget->partner->allowed_domains, true)
                : $partnerWidget->partner->allowed_domains;
        }

        // Permite localhost em desenvolvimento
        if (app()->environment('local') && in_array($domain, ['localhost', '127.0.0.1'])) {
            return ['error' => false];
        }

        if (!in_array($domain, $allowedDomains)) {
            return [
                'error' => true,
                'response' => response()->json([
                    'error' => 'Domínio não autorizado',
                    'domain' => $domain,
                    'allowed' => $allowedDomains
                ], 403)
            ];
        }

        return ['error' => false];
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Valida token
        $validation = $this->validateToken($request);

        if ($validation['error']) {
            return $validation['response'];
        }

        $partnerWidget = $validation['partnerWidget'];

        // 2. Valida domínio
        $domainValidation = $this->validateDomain($request, $partnerWidget);

        if ($domainValidation['error']) {
            return $domainValidation['response'];
        }

        // 3. Anexa dados ao request
        $request->merge([
            'partner' => $partnerWidget->partner,
            'widget' => $partnerWidget->widget,
            'partner_widget' => $partnerWidget
        ]);

        // 4. Atualiza estatísticas (opcional)
        $partnerWidget->update([
            'last_used_at' => now(),
            'total_calculations' => $partnerWidget->total_calculations + 1
        ]);

        return $next($request);
    }
}
