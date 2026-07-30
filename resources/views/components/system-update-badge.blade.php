@php
    $updateInfo = app(\App\Services\CoreUpdateService::class)->getUpdateInfo();
@endphp

<span class="system-update-wrap" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-left: 8px;">
    @if($updateInfo['has_update'])
        <a href="#"
           onclick="triggerSystemUpdate(event, '{{ $updateInfo['latest_version'] }}')"
           style="color: #10b981; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
           title="Clique para atualizar para a versão v{{ $updateInfo['latest_version'] }}">
            <x-lucide-cloud-download class="lucid-icon" style="width: 15px; height: 15px; color: #10b981;" />
            <span>Baixar v{{ $updateInfo['latest_version'] }}</span>
        </a>
    @else
        <span style="color: #6b7280; display: inline-flex; align-items: center; gap: 4px;" title="Sistema na versão mais recente">
            <x-lucide-check class="lucid-icon" style="width: 14px; height: 14px; color: #10b981;" />
            <small style="opacity: 0.8;">Atualizado</small>
        </span>
    @endif
</span>

{{-- Backdrop / Modal de Bloqueio (Escondido por padrão com display: none !important) --}}
@once
@push('styles')
<style>
    .system-update-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.88);
        backdrop-filter: blur(4px);
        z-index: 999999;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        text-align: center;
    }

    .system-update-spinner {
        width: 48px;
        height: 48px;
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

{{-- 💡 O 'display: none !important' direto no atributo style impede qualquer ocupação de espaço na página --}}
<div id="system-update-backdrop" class="system-update-overlay" style="display: none !important;">
    <div class="system-update-spinner"></div>
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #fff;">Atualizando o Lunar Base...</h2>
    <p style="color: #94a3b8; font-size: 0.95rem; max-width: 420px; line-height: 1.5;">
        Baixando o pacote do GitHub e atualizando os arquivos do núcleo do sistema. Por favor, <strong>não feche nem recarregue a página</strong>.
    </p>
</div>

@push('scripts')
<script>
    async function triggerSystemUpdate(event, targetVersion) {
        event.preventDefault();

        if (!confirm(`Deseja iniciar a atualização automática do Lunar Base para a versão v${targetVersion}?`)) {
            return;
        }

        const backdrop = document.getElementById('system-update-backdrop');
        backdrop.style.setProperty('display', 'flex', 'important');

        // Ativa a trava do navegador contra fechamento/navegação acidental
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
                alert('Lunar Base atualizado com sucesso!');
                window.location.reload();
            } else {
                alert('Erro na atualização: ' + (data.message || 'Falha ao aplicar arquivo.'));
                backdrop.style.setProperty('display', 'none', 'important');
            }
        } catch (error) {
            window.removeEventListener('beforeunload', preventUnload);
            alert('Erro de conexão durante o processo de atualização.');
            backdrop.style.setProperty('display', 'none', 'important');
        }
    }
</script>
@endpush
@endonce
