@extends('admin.layout')

@section('header_title', 'Novo Usuário')
@section('header_subtitle', 'Adicione um novo usuário ao sistema')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-user class="lucid-icon" /> Novo Usuário</h2>
        <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    @include('admin.users._form', [
        'action' => route('admin.users.store')
    ])
</div>
@endsection
