@extends('admin.layout')
@section('header_title', 'Detalhes da Resposta')
@section('header_subtitle', 'ID: #' . $submission->id . ' | Form: ' . $form->title)
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        {{-- <div style="display: flex; align-items: center; gap: 1rem;"> --}}
            <h2><x-lucide-file-text class="lucid-icon" /> Detalhes da Submissão</h2>
        {{-- </div> --}}
        {{-- <div style="display: flex; gap: 0.5rem;"> --}}
            <span style="font-size: 0.875rem; color: #6b7280; align-self: center; margin-right: 1rem;">
                Recebido em: {{ $submission->created_at->format('d/m/Y \à\s H:i') }}
            </span>
            <div class="top-buttons">
                <a href="{{ route('admin.forms.submissions.index', $form->id) }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-arrow-left class="lucid-icon" />
                    Voltar
                </a>
            </div>
        {{-- </div> --}}
    </div>

    <div class="settings-group" style="border-bottom: none;">
        {{-- Loop Inteligente: Usa o Schema para traduzir as chaves --}}
        @foreach($form->fields_schema as $field)
            @php
                $key = $field['key'];
                $value = $submission->data[$key] ?? null;

                // Formatação básica de saída
                if (is_null($value)) {
                    $displayValue = '<span style="color: #9ca3af;">Não preenchido</span>';
                } elseif (is_array($value)) {
                    $displayValue = '<ul style="margin: 0; padding-left: 1.2rem; list-style-type: disc;">' .
                                    collect($value)->map(fn($v) => "<li>{$v}</li>")->implode('') .
                                    '</ul>';
                } elseif (is_bool($value)) {
                    $displayValue = $value ? '<span style="color: #059669; font-weight: 500;">Sim</span>' : '<span style="color: #dc2626; font-weight: 500;">Não</span>';
                } else {
                    // Converte quebras de linha em <br> para textareas
                    $displayValue = nl2br(e($value));
                }
            @endphp

            <div class="form-group" style="display: grid; grid-template-columns: 250px 1fr; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem;">
                <div>
                    <label class="field-label" style="color: #6b7280; font-size: 0.875rem;">
                        {{ $field['label'] }}
                    </label>
                    @if(!empty($field['description']))
                        <small class="form-help">{{ $field['description'] }}</small>
                    @endif
                </div>
                <div style="color: #1f2937; font-size: 0.95rem; word-break: break-word;">
                    {!! $displayValue !!}
                </div>
            </div>
        @endforeach

        {{-- Metadados técnicos --}}
        <div class="form-group" style="display: grid; grid-template-columns: 250px 1fr; gap: 1rem; margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #e5e7eb;">
            <div>
                <label class="field-label" style="color: #6b7280; font-size: 0.875rem;">IP do Usuário</label>
            </div>
            <div style="color: #1f2937; font-family: monospace;">
                {{ $submission->ip_address ?? 'Desconhecido' }}
            </div>
        </div>
    </div>
    <div class="buttons">
        <form method="POST" action="{{ route('admin.forms.submissions.destroy', [$form->id, $submission->id]) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">
                <x-lucide-trash-2 class="lucid-icon" /> Excluir
            </button>
        </form>
    </div>
</div>
@endsection
