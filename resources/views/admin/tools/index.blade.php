@extends('admin.layout')
@section('header_title', 'Ferramentas')
@section('header_subtitle', 'Ferramentas úteis para manutenção do sistema')

@push('styles')
<style>
    /* ===== SEÇÃO EXPLICATIVA (INTRO) ===== */
    .tools-intro {
        background: rgba(0, 0, 0, 0.02);
        border: 1px dashed var(--color-border);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .tools-intro h3 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.125rem;
        color: var(--color-text-dark, #1f2937);
    }
    .tools-intro p {
        margin: 0;
        font-size: 0.9rem;
        color: var(--color-text-muted, #4b5563);
        line-height: 1.5;
    }

    /* ===== GRID DE CARTÕES ===== */
    .tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
    }

    /* ===== CARTÃO INDIVIDUAL (CARD) ===== */
    .tools-card {
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
    .tools-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    /* Cabeçalho do Card (Ícone + Título) */
    .tools-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .tools-card-icon {
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
    .tools-card-icon .lucid-icon {
        width: 24px;
        height: 24px;
    }
    .tools-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: var(--color-text-dark, #1f2937);
    }

    /* Corpo do Card (Descrição) */
    .tools-card-body {
        flex: 1;
        margin-bottom: 1.5rem;
    }
    .tools-card-desc {
        font-size: 0.875rem;
        color: var(--color-text-muted, #6b7280);
        line-height: 1.5;
        margin: 0;
    }

    /* Rodapé do Card (Botão de Ação) */
    .tools-card-footer {
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
<div class="admin-card">

    {{-- Bloco Introdutório --}}
    <div class="tools-intro">
        <h3><x-lucide-wrench class="lucid-icon" /> Ferramentas do Sistema</h3>
        <p>
            Esta é a área de ferramentas do Lunar Base.
        </p>
    </div>

    {{-- Grid de Subitens --}}
    <div class="tools-grid">

        {{-- Card 1: Hooks do Sistema --}}
        <div class="tools-card">
            <div class="tools-card-header">
                <div class="tools-card-icon">
                    <x-lucide-folder-sync class="lucid-icon" />
                </div>
                <h4 class="tools-card-title">Exportar/importar</h4>
            </div>
            <div class="tools-card-body">
                <p class="tools-card-desc">
                    Exporte ou importe posts, páginas, taxonomias e outros conteúdos
                </p>
            </div>
            <div class="tools-card-footer">
                <a href="{{ route('admin.tools.content-transfer.index') }}" class="admin-btn admin-btn-secondary">
                    Exportar ou importar
                </a>
            </div>
        </div>

        <x-hook name="admin.tools_page" desc="Página de ferramentas do sistema" />

    </div>
</div>
@endsection
