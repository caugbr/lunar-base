@extends('admin.layout')

@section('header_title', 'Novo Aviso')
@section('header_subtitle', 'Crie um alerta persistente para os usuários do painel')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-bell-plus class="lucid-icon" /> Novo Aviso</h2>
        <a href="{{ route('admin.notices.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    @include('admin.notices._form', [
        'action'      => route('admin.notices.store'),
        'method'      => 'POST',
        'submitLabel' => 'Criar aviso'
    ])
</div>
@endsection
