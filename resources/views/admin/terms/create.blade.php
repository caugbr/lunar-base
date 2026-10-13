@extends('admin.layout')

@section('header_title', 'Novo Termo')
@section('header_subtitle', 'Crie um novo termo')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tag class="lucid-icon" /> Novo Termo</h2>
        <a href="{{ route('admin.terms.index', ['taxonomy_id' => $selectedTaxonomy->id ?? null]) }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    @include('admin.terms._form', [
        'action' => route('admin.terms.store')
    ])
</div>
@endsection
