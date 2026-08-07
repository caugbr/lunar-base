<?php

if (! function_exists('currentSiteData')) {
    /**
     * Busca os dados do mapeamento do domínio atual nas settings.
     */
    function currentSiteData(): ?array
    {
        $host = request()->getHost();

        foreach (siteDomains() as $item) {
            $configuredDomain = trim($item['domain'] ?? '');
            $namespace = trim($item['namespace'] ?? '');

            if (empty($configuredDomain) && empty($namespace)) {
                continue;
            }

            if (
                ($configuredDomain && $host === $configuredDomain) ||
                ($configuredDomain && str_contains($host, $configuredDomain)) ||
                ($namespace && str_contains($host, $namespace))
            ) {
                return $item;
            }
        }

        return null;
    }
}


if (! function_exists('currentSiteDomain')) {
    /**
     * Retorna apenas a string do domínio/subdomínio atual.
     * Exemplo: 'parceiros.lunarapps.com.br'
     */
    function currentSiteDomain(): string
    {
        $data = currentSiteData();

        return !empty($data['domain']) ? $data['domain'] : request()->getHost();
    }
}

if (! function_exists('currentNamespace')) {
    /**
     * Retorna apenas a string do namespace do site atual.
     * Exemplo: 'parceiros', 'loja' ou 'default'
     */
    function currentNamespace(string $default = 'default'): string
    {
        $data = currentSiteData();

        return !empty($data['namespace']) ? $data['namespace'] : $default;
    }
}

if (! function_exists('isSiteNamespace')) {
    /**
     * Retorna um boolean indicando se o namespace atual é o informado.
     * Exemplo: if (isSiteNamespace('parceiros'))
     */
    function isSiteNamespace(string $namespace): bool
    {
        return currentNamespace() === $namespace;
    }
}

if (! function_exists('siteDomains')) {
    /**
     * Retorna a lista completa de domínios extras cadastrados nas configurações.
     *
     * @return array
     */
    function siteDomains(): array
    {
        $domains = setting('multisite.extra_domains', []);

        // Garante a decodificação se o valor estiver em formato JSON no banco
        if (is_string($domains)) {
            $domains = json_decode($domains, true) ?? [];
        }

        return is_array($domains) ? $domains : [];
    }
}
