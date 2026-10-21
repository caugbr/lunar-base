@php
    $updateInfo = app(\App\Services\CoreUpdateService::class)->getUpdateInfo();
@endphp

<span class="system-update-wrap" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-left: 8px;">
    @if($updateInfo['has_update'])
        <a href="{{ route('admin.system.update.index') }}"
           style="color: #10b981; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
           title="Clique para ver as novidades da versão v{{ $updateInfo['latest_version'] }}">
            <x-lucide-cloud-download class="lucid-icon" style="width: 15px; height: 15px; color: #10b981;" />
            <span>Versão {{ $updateInfo['latest_version'] }} disponível</span>
        </a>
    @else
        <a href="{{ route('admin.system.update.index') }}"
           style="color: #6b7280; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;"
           title="Verificar atualizações do sistema">
            <x-lucide-check class="lucid-icon" style="width: 14px; height: 14px; color: #10b981;" />
            <small style="opacity: 0.8;">{{ $updateInfo['current_version'] }} (Atualizado)</small>
        </a>
    @endif
</span>
