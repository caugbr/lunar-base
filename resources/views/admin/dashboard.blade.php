@extends('admin.layout')

@section('header_title', 'Dashboard')
@section('header_subtitle', 'Visão geral do sistema')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2>
            <x-lucide-layout-dashboard class="lucid-icon" />
            Visão Geral
        </h2>
    </div>

    <div class="lunar-dashboard-grid">
        <a href="{{ route('admin.pages.index') }}" class="lunar-stat-box">
            <div class="stat-icon icon-pages">
                <x-lucide-file class="lucid-icon" />
            </div>
            <div class="stat-details">
                <span class="stat-number">{{ $stats['pages'] }}</span>
                <span class="stat-label">Páginas Dinâmicas</span>
            </div>
            <div class="stat-arrow">
                <x-lucide-arrow-right class="lucid-icon" />
            </div>
        </a>

        <a href="{{ route('admin.posts.index') }}" class="lunar-stat-box">
            <div class="stat-icon icon-posts">
                <x-lucide-newspaper class="lucid-icon" />
            </div>
            <div class="stat-details">
                <span class="stat-number">{{ $stats['posts'] }}</span>
                <span class="stat-label">Posts no Blog</span>
            </div>
            <div class="stat-arrow">
                <x-lucide-arrow-right class="lucid-icon" />
            </div>
        </a>

        {{-- <a href="{{ route('admin.forms.index') }}" class="lunar-stat-box">
            <div class="stat-icon icon-forms">
                <x-lucide-form-input class="lucid-icon" />
            </div>
            <div class="stat-details">
                <span class="stat-number">{{ $stats['forms'] }}</span>
                <span class="stat-label">Formulários Ativos</span>
            </div>
            <div class="stat-arrow">
                <x-lucide-arrow-right class="lucid-icon" />
            </div>
        </a> --}}

        <a href="{{ route('admin.media.index') }}" class="lunar-stat-box">
            <div class="stat-icon icon-media">
                <x-lucide-image class="lucid-icon" />
            </div>
            <div class="stat-details">
                <span class="stat-number">{{ $stats['media'] }}</span>
                <span class="stat-label">Arquivos de Mídia</span>
            </div>
            <div class="stat-arrow">
                <x-lucide-arrow-right class="lucid-icon" />
            </div>
        </a>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.users.index') }}" class="lunar-stat-box">
            <div class="stat-icon icon-users">
                <x-lucide-users class="lucid-icon" />
            </div>
            <div class="stat-details">
                <span class="stat-number">{{ $stats['users'] }}</span>
                <span class="stat-label">Usuários Cadastrados</span>
            </div>
            <div class="stat-arrow">
                <x-lucide-arrow-right class="lucid-icon" />
            </div>
        </a>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush
