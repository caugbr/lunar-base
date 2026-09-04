@if(!empty($composerDeps) || !empty($phpVersion))
<div class="plugin-dependencies-help" style="margin-top: 1.5rem;">
    <h4 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
        <x-lucide-package-check class="lucid-icon" /> Dependências e Requisitos do Plugin
    </h4>
    <p style="font-size: 0.875rem; color: var(--color-text-muted, #64748b); margin-bottom: 0.75rem;">
        Este plugin necessita dos seguintes pacotes/requisitos para operar com todos os recursos:
    </p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 12px;">
        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.875rem;">
            @if($phpVersion)
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 4px 0;">
                    <span><strong>PHP:</strong> <code>{{ $phpVersion }}</code></span>
                    @php $phpOk = version_compare(PHP_VERSION, preg_replace('/[^0-9.]/', '', $phpVersion), '>='); @endphp
                    <span style="font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; background: {{ $phpOk ? '#dcfce7; color: #166534;' : '#fee2e2; color: #991b1b;' }}">
                        {{ $phpOk ? '✓ Compatível (' . PHP_VERSION . ')' : '✗ Incompatível' }}
                    </span>
                </li>
            @endif

            @foreach($composerDeps as $package => $version)
                @php
                    // Verifica se o pacote está instalado no vendor
                    $isInstalled = class_exists(str_replace('/', '\\', ucwords($package, '/')))
                                || File::exists(base_path("vendor/{$package}"));
                @endphp
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-top: 1px solid #f1f5f9;">
                    <span>
                        <strong>{{ $package }}</strong>: <code>{{ $version }}</code>
                    </span>
                    <span style="font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; background: {{ $isInstalled ? '#dcfce7; color: #166534;' : '#fef3c7; color: #92400e;' }}">
                        {{ $isInstalled ? '✓ Instalado' : '⚡ Instalação necessária' }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    @php
        // Monta o comando de instalação para facilitar o copia-e-cola
        $packagesString = implode(' ', array_map(fn($pkg, $ver) => "\"{$pkg}:{$ver}\"", array_keys($composerDeps), array_values($composerDeps)));
    @endphp

    @if(!empty($composerDeps))
        <p style="font-size: 0.8rem; color: var(--color-text-muted, #64748b); margin-bottom: 0.4rem;">
            Para instalar via terminal no diretório raiz do projeto:
        </p>
        <div class="code" style="font-family: monospace; padding: 10px 14px; background: #1e293b; color: #38bdf8; border-radius: 6px; font-size: 0.85rem; display: flex; align-items: center; justify-content: space-between; overflow-x: auto;">
            <span>composer require {{ $packagesString }}</span>
        </div>
    @endif
</div>
@endif
