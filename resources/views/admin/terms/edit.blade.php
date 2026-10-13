@extends('admin.layout')

@section('header_title', 'Editar Termo')
@section('header_subtitle', 'Modifique os dados do termo')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tag class="lucid-icon" /> Editar: {{ $term->name }}</h2>
        <a href="{{ route('admin.terms.index', ['taxonomy_id' => $term->taxonomy_id]) }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    @include('admin.terms._form', [
        'action' => route('admin.terms.update', $term->id)
    ])
</div>
@endsection
