@extends('admin.layout')

@section('header_title', 'Transferência de Conteúdo')
@section('header_subtitle', 'Mova posts, páginas e taxonomias entre sites via JSON')

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">

    {{-- BLOCO DE EXPORTAÇÃO --}}
    <div class="admin-card">
        <div class="admin-card-header">
            <h2><x-lucide-file-up class="lucid-icon" /> Exportar Dados</h2>
        </div>

        <form method="POST" action="{{ route('admin.tools.content-transfer.export') }}">
            @csrf
            <p class="admin-text-muted" style="margin-bottom: 1.5rem;">Selecione os conteúdos que deseja baixar em um arquivo <code>.json</code>:</p>

            <div style="display: flex; flex-direction: column; gap: 0.8rem; margin-bottom: 2rem;">
                <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="export_types[]" value="taxonomies" checked>
                    <span><strong>Taxonomias e Termos</strong> (Categorias, Tags, etc.)</span>
                </label>

                @foreach($types as $key => $label)
                    <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="export_types[]" value="{{ $key }}" checked>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="buttons" style="justify-content: flex-start;">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-download class="lucid-icon" /> Gerar Arquivo de Exportação
                </button>
            </div>
        </form>
    </div>

    {{-- BLOCO DE IMPORTAÇÃO --}}
    <div class="admin-card">
        <div class="admin-card-header">
            <h2><x-lucide-file-down class="lucid-icon" /> Importar Dados</h2>
        </div>

        <form method="POST" action="{{ route('admin.tools.content-transfer.import') }}" enctype="multipart/form-data" data-confirm="ATENÇÃO: A importação pode alterar dados existentes. Deseja continuar?">
            @csrf
            <p class="admin-text-muted" style="margin-bottom: 1.5rem;">Selecione um arquivo <code>.json</code> de exportação do Lunar Base para processar:</p>

            <div class="form-group">
                <x-upload-area name="import_file" id="import_file" accept=".json" required="true" label="Arquivo JSON" />
            </div>

            <div class="form-group">
                <label><strong>Estratégia para duplicatas (slugs iguais):</strong></label>
                <div>
                    <label>
                        <input type="radio" name="strategy" value="skip" checked>
                        <span>Ignorar (Não importar se o slug já existir)</span>
                    </label>
                    <label>
                        <input type="radio" name="strategy" value="overwrite">
                        <span>Sobrescrever (Atualizar conteúdo existente)</span>
                    </label>
                    <label>
                        <input type="radio" name="strategy" value="draft">
                        <span>Importar como Novo Rascunho (Renomeia o slug)</span>
                    </label>
                </div>
            </div>

            <div class="buttons">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-upload class="lucid-icon" /> Iniciar Importação
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
