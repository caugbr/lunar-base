@extends('admin.layout')

@section('header_title', 'Atualização do Sistema')
@section('header_subtitle', 'Verifique novidades da versão e atualize o núcleo do Lunar Base')

@section('content')
<div class="admin-card">
    {{-- Topo do Card --}}
    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="display: flex; align-items: center; gap: 8px; margin: 0;">
            <x-lucide-refresh-cw class="lucid-icon" /> Atualizações do Core
        </h2>
        <a href="{{ route('admin.system.update.check') }}" class="admin-btn admin-btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <x-lucide-rotate-cw class="lucid-icon" style="width: 16px; height: 16px;" /> Verificar Novamente
        </a>
    </div>

    <div class="admin-card-body" style="padding: 1.5rem;">
        {{-- BLOCO 1: Resumo das Versões e Ação --}}
        <div class="update-status-panel" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 1.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 2rem;">
            @if($updateInfo['has_update'])
                {{-- Cenário COM Atualização: Comparativo e Ação --}}
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                    <div>
                        <span style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600; display: block;">Versão Atual</span>
                        <strong style="font-size: 1.15rem; color: #1e293b;">{{ $updateInfo['current_version'] }}</strong>
                    </div>

                    <div style="color: #cbd5e1; font-size: 1.25rem;">→</div>

                    <div>
                        <span style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600; display: block;">Nova Versão</span>
                        <strong style="font-size: 1.15rem; color: #10b981;">{{ $updateInfo['latest_version'] }}</strong>
                    </div>

                    <div>
                        <span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600;">
                            Nova versão disponível!
                        </span>
                    </div>
                </div>

                {{-- Botão de Disparo da Atualização --}}
                <button type="button"
                        onclick="triggerSystemUpdate(event, '{{ $updateInfo['latest_version'] }}')"
                        class="admin-btn admin-btn-primary"
                        style="background: #10b981; border-color: #10b981; color: #fff; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; padding: 0.65rem 1.25rem; cursor: pointer;">
                    <x-lucide-cloud-download class="lucid-icon" style="width: 18px; height: 18px;" />
                    Atualizar para a versão {{ $updateInfo['latest_version'] }}
                </button>
            @else
                {{-- Cenário SEM Atualização: Apenas a versão do sistema e a confirmação --}}
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                    <div>
                        <span style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600; display: block;">Versão do Sistema</span>
                        <strong style="font-size: 1.15rem; color: #1e293b;">{{ $updateInfo['current_version'] }}</strong>
                    </div>

                    <div style="display: inline-flex; align-items: center; gap: 6px; color: #10b981; font-weight: 600; font-size: 0.95rem;">
                        <x-lucide-check-circle class="lucid-icon" style="width: 18px; height: 18px; color: #10b981;" />
                        <span>Seu sistema está atualizado!</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- BLOCO 2: Título do Changelog --}}
        <div style="margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <x-lucide-file-text class="lucid-icon" style="width: 18px; height: 18px; color: #64748b;" />
                Notas da Versão {{ $updateInfo['target_version'] }}
            </h3>
        </div>

        {{-- BLOCO 3: Conteúdo Formatado do Changelog --}}
        <div class="changelog-content" style="line-height: 1.7; color: #334155;">
            {!! $updateInfo['changelog_html'] !!}
        </div>
    </div>
</div>

{{-- Estilos dedicados para formatar o Changelog Markdown perfeitamente --}}
@push('styles')
<style>
    .changelog-content h3 {
        font-size: 0.95rem;
        font-weight: 700;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
    }

    /* Badges semânticos para cada tipo do Keep a Changelog */
    .changelog-content h3:has(+ ul):contains("Added"),
    .changelog-content h3:contains("Added") {
        background-color: #dcfce7;
        color: #166534;
    }

    .changelog-content h3:contains("Changed") {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .changelog-content h3:contains("Fixed") {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .changelog-content ul {
        margin: 0 0 1.25rem 1.25rem;
        padding: 0;
    }

    .changelog-content li {
        margin-bottom: 0.4rem;
    }

    /* Backdrop / Overlay de Atualização */
    .system-update-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(4px);
        z-index: 999999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        text-align: center;
    }

    .system-update-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255, 255, 255, 0.2);
        border-left-color: #10b981;
        border-radius: 50%;
        animation: spin-update 1s linear infinite;
        margin-bottom: 1.5rem;
    }

    @keyframes spin-update {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

{{-- Backdrop de Bloqueio --}}
<div id="system-update-backdrop" class="system-update-overlay" style="display: none !important;">
    <div class="system-update-spinner"></div>
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #fff;">Atualizando o Lunar Base...</h2>
    <p style="color: #94a3b8; font-size: 0.95rem; max-width: 440px; line-height: 1.5;">
        Baixando a versão do GitHub, aplicando migrações e atualizando o núcleo. Por favor, <strong>não feche nem recarregue esta página</strong>.
    </p>
</div>

@push('scripts')
<script>
    async function triggerSystemUpdate(event, targetVersion) {
        event.preventDefault();

        const confirmed = await Dialog.confirm(`Deseja iniciar a atualização automática do Lunar Base para a versão ${targetVersion}?`);
        if (!confirmed) {
            return;
        }

        const backdrop = document.getElementById('system-update-backdrop');
        backdrop.style.setProperty('display', 'flex', 'important');

        // Impede fechar a aba acidentalmente durante a gravação de arquivos
        const preventUnload = (e) => {
            e.preventDefault();
            e.returnValue = 'Atualização em andamento. Não feche a página!';
            return e.returnValue;
        };
        window.addEventListener('beforeunload', preventUnload);

        try {
            const response = await fetch("{{ route('admin.system.update.apply') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            window.removeEventListener('beforeunload', preventUnload);

            if (response.ok && data.success) {
                backdrop.style.setProperty('display', 'none', 'important');
                await Dialog.alert('Lunar Base atualizado com sucesso!');
                window.location.reload();
            } else {
                Dialog.alert('Erro na atualização: ' + (data.message || 'Falha ao aplicar arquivo.'));
                backdrop.style.setProperty('display', 'none', 'important');
            }
        } catch (error) {
            window.removeEventListener('beforeunload', preventUnload);
            Dialog.alert('Erro de conexão durante o processo de atualização.');
            backdrop.style.setProperty('display', 'none', 'important');
        }
    }
</script>
@endpush
@endsection
