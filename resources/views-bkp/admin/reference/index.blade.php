@extends('admin.layout')
@section('header_title', 'Referências do Sistema')
@section('header_subtitle', 'Documentação, logs e referências para desenvolvedores e administradores')

@once
@push('styles')
<style>
    /* ===== SEÇÃO EXPLICATIVA (INTRO) ===== */
    .reference-intro {
        background: rgba(0, 0, 0, 0.02);
        border: 1px dashed var(--color-border);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .reference-intro h3 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.125rem;
        color: var(--color-text-dark, #1f2937);
    }
    .reference-intro p {
        margin: 0;
        font-size: 0.9rem;
        color: var(--color-text-muted, #4b5563);
        line-height: 1.5;
    }

    /* ===== GRID DE CARTÕES ===== */
    .reference-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
    }

    /* ===== CARTÃO INDIVIDUAL (CARD) ===== */
    .reference-card {
        background: #ffffff;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .reference-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    /* Cabeçalho do Card (Ícone + Título) */
    .reference-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .reference-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: var(--color-bg-dark, #f3f4f6);
        border-radius: 8px;
        color: var(--color-primary, #2563eb);
        flex-shrink: 0;
    }
    .reference-card-icon .lucid-icon {
        width: 24px;
        height: 24px;
    }
    .reference-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: var(--color-text-dark, #1f2937);
    }

    /* Corpo do Card (Descrição) */
    .reference-card-body {
        flex: 1;
        margin-bottom: 1.5rem;
    }
    .reference-card-desc {
        font-size: 0.875rem;
        color: var(--color-text-muted, #6b7280);
        line-height: 1.5;
        margin: 0;
    }

    /* Rodapé do Card (Botão de Ação) */
    .reference-card-footer {
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush
@endonce

@section('content')
<div class="admin-card">

    {{-- Bloco Introdutório --}}
    <div class="reference-intro">
        <h3><x-lucide-terminal class="lucid-icon" /> Central de Referência do Sistema</h3>
        <p>
            Bem-vindo à área de referências do Lunar Base. Este espaço existe para centralizar as áreas de monitoramento do sistema. Aqui você pode visualizar ganchos de extensão ativos, consultar shortcodes registrados, auditar as permissões de papéis de usuários e acompanhar o histórico de atividades e auditoria de ações realizadas no painel administrativo.
        </p>
    </div>

    {{-- Grid de Subitens --}}
    <div class="reference-grid">

        {{-- Card 1: Hooks do Sistema --}}
        <div class="reference-card">
            <div class="reference-card-header">
                <div class="reference-card-icon">
                    <x-lucide-fishing-hook class="lucid-icon" />
                </div>
                <h4 class="reference-card-title">Hooks do Sistema</h4>
            </div>
            <div class="reference-card-body">
                <p class="reference-card-desc">
                    Consulte os pontos de ancoragem ativos mapeados nas views. Ideal para verificar onde os seus temas e plugins podem injetar marcações ou substituir elementos de forma transparente.
                </p>
            </div>
            <div class="reference-card-footer">
                {{-- 💡 Nota: Ajuste os nomes das rotas abaixo de acordo com seu arquivo de rotas --}}
                <a href="{{ route('admin.hooks') }}" class="admin-btn admin-btn-secondary">
                    Ver Hooks
                </a>
            </div>
        </div>

        {{-- Card 2: Shortcodes do Sistema --}}
        <div class="reference-card">
            <div class="reference-card-header">
                <div class="reference-card-icon">
                    <x-lucide-brackets class="lucid-icon" />
                </div>
                <h4 class="reference-card-title">Shortcodes</h4>
            </div>
            <div class="reference-card-body">
                <p class="reference-card-desc">
                    Consulte os códigos de atalho registrados no core do sistema e por plugins ativos. Útil para verificar parâmetros de incorporação de mídias e tags customizadas.
                </p>
            </div>
            <div class="reference-card-footer">
                <a href="{{ route('admin.shortcodes') }}" class="admin-btn admin-btn-secondary">
                    Ver Shortcodes
                </a>
            </div>
        </div>

        {{-- Card 3: Permissões de Acesso --}}
        <div class="reference-card">
            <div class="reference-card-header">
                <div class="reference-card-icon">
                    <x-lucide-shield-check class="lucid-icon" />
                </div>
                <h4 class="reference-card-title">Permissões</h4>
            </div>
            <div class="reference-card-body">
                <p class="reference-card-desc">
                    Monitore os níveis de acesso à administração. Entenda quais papéis de usuários do sistema possuem privilégios de leitura, escrita e outras configurações no core.
                </p>
            </div>
            <div class="reference-card-footer">
                <a href="{{ route('admin.roles-permissions') }}" class="admin-btn admin-btn-secondary">
                    Ver Permissões
                </a>
            </div>
        </div>

        {{-- Card 4: Logs da Aplicação --}}
        <div class="reference-card">
            <div class="reference-card-header">
                <div class="reference-card-icon">
                    <x-lucide-file-text class="lucid-icon" />
                </div>
                <h4 class="reference-card-title">Logs de Auditoria</h4>
            </div>
            <div class="reference-card-body">
                <p class="reference-card-desc">
                    Acompanhe o histórico detalhado de ações e alterações executadas pelos usuários no painel administrativo. Essencial para rastrear quem criou, modificou ou excluiu registros no sistema, garantindo segurança e rastreabilidade.
                </p>
            </div>
            <div class="reference-card-footer">
                <a href="{{ route('admin.logs') }}" class="admin-btn admin-btn-secondary">
                    Auditar Atividades
                </a>
            </div>
        </div>

        <div class="reference-card">
            <div class="reference-card-header">
                <div class="reference-card-icon">
                    <x-lucide-school class="lucid-icon" />
                </div>
                <h4 class="reference-card-title">Tutoriais do Sistema</h4>
            </div>
            <div class="reference-card-body">
                <p class="reference-card-desc">
                    Preparamos alguns tutoriais para quem vai usar o sistema ou desenvolver plugins e temas para o {{ config('app.name') }}.
                </p>
            </div>
            <div class="reference-card-footer">
                <a href="/tutorials/index.html" class="admin-btn admin-btn-secondary" target="_blank">
                    <x-lucide-external-link class="lucid-icon" />
                    Ver Tutoriais
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
