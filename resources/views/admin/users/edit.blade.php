@extends('admin.layout')

@section('header_title', 'Editar Usuário')
@section('header_subtitle', $user->name)

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-user class="lucid-icon" /> Editar Usuário</h2>
        <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    @include('admin.users._form', [
        'action' => route('admin.users.update', $user->id)
    ])
</div>
@endsection
