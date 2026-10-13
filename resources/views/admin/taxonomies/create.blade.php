@extends('admin.layout')

@section('header_title', 'Nova Taxonomia')
@section('header_subtitle', 'Crie um novo tipo de classificação')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-tags class="lucid-icon" /> Nova Taxonomia</h2>
        <a href="{{ route('admin.taxonomies.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    @include('admin.taxonomies._form', [
        'action' => route('admin.taxonomies.store')
    ])
</div>
@endsection
