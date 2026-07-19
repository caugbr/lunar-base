@extends('admin.layout')

<style>
    .submission-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .received-at {
        font-size: 0.875rem;
        color: var(--color-text);
        margin-right: 1rem;
    }
    .field-row {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--color-border);
        padding-bottom: 1rem;
    }
    .field-label-text {
        color: var(--color-text-muted);
        font-size: 0.875rem;
    }
    .field-value {
        color: var(--color-text);
        font-size: 0.95rem;
        word-break: break-word;
    }
    .meta-row {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1rem;
    }
    .meta-value {
        color: var(--color-text);
        font-family: monospace;
    }
    .list-style {
        margin: 0;
        padding-left: 1.2rem;
        list-style-type: disc;
    }
    .text-gray { color: #9ca3af; }
    .text-green { color: #059669; font-weight: 500; }
    .text-red { color: #dc2626; font-weight: 500; }
</style>

@section('header_title', 'Detalhes da Resposta')
@section('header_subtitle', 'ID: #' . $submission->id . ' | Form: ' . $form->title)

@section('content')
<div class="admin-card">
    <div class="admin-card-header submission-header">
        <h2><x-lucide-file-text class="lucid-icon" /> Detalhes da Submissão</h2>
        <div style="display: flex; align-items: center;">
            <span class="received-at">
                Recebido em: {{ $submission->created_at->format('d/m/Y \à\s H:i') }}
            </span>
            <div class="top-buttons">
                <a href="{{ route('admin.forms.submissions.index', $form->id) }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-arrow-left class="lucid-icon" /> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="settings-group" style="border-bottom: none;">
        @foreach($form->fields_schema as $field)
            @php
                $key = $field['key'];
                $value = $submission->data[$key] ?? null;

                if (is_null($value)) {
                    $displayValue = '<span class="text-gray">Não preenchido</span>';
                } elseif (is_array($value)) {
                    $displayValue = '<ul class="list-style">' . collect($value)->map(fn($v) => "<li>{$v}</li>")->implode('') . '</ul>';
                } elseif (is_bool($value)) {
                    $displayValue = $value ? '<span class="text-green">Sim</span>' : '<span class="text-red">Não</span>';
                } else {
                    $displayValue = nl2br(e($value));
                }
            @endphp

            <div class="field-row">
                <div>
                    <label class="field-label field-label-text">{{ $field['label'] }}</label>
                    @if(!empty($field['description']))
                        <small class="form-help">{{ $field['description'] }}</small>
                    @endif
                </div>
                <div class="field-value">
                    {!! $displayValue !!}
                </div>
            </div>
        @endforeach

        <div class="meta-row">
            <div><label class="field-label field-label-text">IP do Usuário</label></div>
            <div class="meta-value">{{ $submission->ip_address ?? 'Desconhecido' }}</div>
        </div>
    </div>

    <div class="buttons">
        <form method="POST" action="{{ route('admin.forms.submissions.destroy', [$form->id, $submission->id]) }}" data-confirm="Tem certeza que deseja excluir?">
            @csrf @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-danger">
                <x-lucide-trash-2 class="lucid-icon" /> Excluir
            </button>
        </form>
    </div>
</div>
@endsection
