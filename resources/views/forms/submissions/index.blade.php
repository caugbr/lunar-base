@extends('admin.layout')
@section('header_title', 'Respostas')
@section('header_subtitle', 'Formulário: ' . $form->title)
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('admin.forms.index') }}" class="admin-btn admin-btn-secondary" style="padding: 6px 12px;">
                <x-lucide-arrow-left class="lucid-icon" />
            </a>
            <h2><x-lucide-inbox class="lucid-icon" /> {{ $submissions->total() }} Respostas Recebidas</h2>
        </div>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Resumo (Primeiro Campo)</th>
                    <th>IP</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $submission)
                <tr>
                    <td>{{ $submission->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @php
                            // Tenta pegar o valor do primeiro campo definido no schema para usar como resumo
                            $firstFieldKey = $form->fields_schema[0]['key'] ?? null;
                            $summary = $firstFieldKey ? ($submission->data[$firstFieldKey] ?? '—') : '—';
                            if(is_array($summary)) $summary = implode(', ', $summary);
                        @endphp
                        <strong>{{ $summary }}</strong>
                    </td>
                    <td><code style="font-size: 0.8em;">{{ $submission->ip_address }}</code></td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.forms.submissions.show', [$form->id, $submission->id]) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" title="Ver detalhes">
                                <x-lucide-eye class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.forms.submissions.destroy', [$form->id, $submission->id]) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" onclick="return confirm('Excluir esta resposta?')" title="Excluir">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-text-center admin-text-muted">Nenhuma resposta recebida ainda.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $submissions->appends(request()->query())->links() }}
    </div>
</div>
@endsection
