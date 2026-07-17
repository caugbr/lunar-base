@extends('admin.layout')
@section('header_title', 'Populator')
@section('header_subtitle', 'Gere conteudo de teste para popular o site')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/populator/css/populator.css') }}">
@endpush
@endonce

@section('content')

{{-- Dados do site hoje --}}
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-bar-chart-3 class="lucid-icon" /> Dados do site hoje</h2>
    </div>
    <div class="populator-stats">
        <div class="stat-card">
            <x-lucide-users class="lucid-icon" />
            <span class="stat-value">{{ $userCount }}</span>
            <span class="stat-label">Usuarios</span>
        </div>
        <div class="stat-card">
            <x-lucide-file-text class="lucid-icon" />
            <span class="stat-value">{{ $postCount }}</span>
            <span class="stat-label">Posts</span>
        </div>
        <div class="stat-card">
            <x-lucide-layout class="lucid-icon" />
            <span class="stat-value">{{ $pageCount }}</span>
            <span class="stat-label">Paginas</span>
        </div>
        <div class="stat-card">
            <x-lucide-tags class="lucid-icon" />
            <span class="stat-value">{{ $termCount }}</span>
            <span class="stat-label">Termos</span>
        </div>
    </div>
</div>

{{-- 3 boxes lado a lado --}}
<div class="populator-grid">

    {{-- Usuarios --}}
    <div class="admin-card populator-box">
        <div class="admin-card-header">
            <h3><x-lucide-users class="lucid-icon" /> Usuarios</h3>
        </div>
        <form method="POST" action="{{ route('admin.populator.users') }}">
            @csrf
            <div class="populator-fields">
                <div class="form-group">
                    <label for="users_qty">Quantidade</label>
                    <input type="number" name="quantity" id="users_qty" value="10" min="1" max="50" class="form-input" required>
                    <small>Maximo: 50 usuarios (nomes unicos)</small>
                </div>
            </div>
            <div class="buttons">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-user-plus class="lucid-icon" /> Gerar Usuarios
                </button>
            </div>
        </form>
    </div>

    {{-- Posts --}}
    <div class="admin-card populator-box">
        <div class="admin-card-header">
            <h3><x-lucide-file-text class="lucid-icon" /> Posts</h3>
        </div>
        <form method="POST" action="{{ route('admin.populator.posts') }}">
            @csrf
            <div class="populator-fields">
                <div class="form-group">
                    <label for="posts_qty">Quantidade</label>
                    <input type="number" name="quantity" id="posts_qty" value="10" min="1" max="100" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="posts_status">Status</label>
                    <select name="status_mode" id="posts_status" class="form-input">
                        <option value="published_only">Somente Publicados</option>
                        <option value="draft_only">Somente Rascunhos</option>
                        <option value="mixed" selected>Publicados e Rascunhos</option>
                    </select>
                </div>
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="with_thumbnail" value="1" checked>
                        <span>Com thumbnail</span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="posts_content_images">Imagens no conteudo (max)</label>
                    <input type="number" name="content_images" id="posts_content_images" value="3" min="0" max="10" class="form-input">
                </div>
                <div class="form-group">
                    <label for="posts_gallery">Imagens na galeria (max)</label>
                    <input type="number" name="gallery_images" id="posts_gallery" value="5" min="0" max="20" class="form-input">
                </div>
            </div>
            <div class="buttons">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-plus class="lucid-icon" /> Gerar Posts
                </button>
            </div>
        </form>
    </div>

    {{-- Paginas --}}
    <div class="admin-card populator-box">
        <div class="admin-card-header">
            <h3><x-lucide-layout class="lucid-icon" /> Paginas</h3>
        </div>
        <form method="POST" action="{{ route('admin.populator.pages') }}">
            @csrf
            <div class="populator-fields">
                <div class="form-group">
                    <label for="pages_qty">Quantidade</label>
                    <input type="number" name="quantity" id="pages_qty" value="5" min="1" max="100" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="pages_status">Status</label>
                    <select name="status_mode" id="pages_status" class="form-input">
                        <option value="published_only">Somente Publicados</option>
                        <option value="draft_only">Somente Rascunhos</option>
                        <option value="mixed" selected>Publicados e Rascunhos</option>
                    </select>
                </div>
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="with_thumbnail" value="1" checked>
                        <span>Com thumbnail</span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="pages_content_images">Imagens no conteudo (max)</label>
                    <input type="number" name="content_images" id="pages_content_images" value="2" min="0" max="10" class="form-input">
                </div>
                <div class="form-group">
                    <label for="pages_gallery">Imagens na galeria (max)</label>
                    <input type="number" name="gallery_images" id="pages_gallery" value="3" min="0" max="20" class="form-input">
                </div>
            </div>
            <div class="buttons">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-plus class="lucid-icon" /> Gerar Paginas
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
