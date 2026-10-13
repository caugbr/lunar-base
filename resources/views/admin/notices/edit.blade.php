@extends('admin.layout')

@section('header_title', 'Editar aviso')
@section('header_subtitle', 'Atualize as informações do aviso do painel')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-pencil class="lucid-icon" /> Editar aviso #{{ $notice->id }}</h2>
        <a href="{{ route('admin.notices.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    @include('admin.notices._form', [
        'action'      => route('admin.notices.update', $notice->id),
        'method'      => 'PUT',
        'submitLabel' => 'Atualizar aviso'
    ])
</div>
@endsection
