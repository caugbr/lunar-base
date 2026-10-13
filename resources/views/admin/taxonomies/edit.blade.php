@extends('admin.layout')

@section('header_title', 'Editar Taxonomia')
@section('header_subtitle', 'Modifique os dados da taxonomia')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tags class="lucid-icon" /> Editar: {{ $taxonomy->name }}</h2>
        <a href="{{ route('admin.taxonomies.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    @include('admin.taxonomies._form', [
        'action' => route('admin.taxonomies.update', $taxonomy->id)
    ])
</div>
@endsection
