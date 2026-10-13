@extends('admin.layout')

@section('header_title', 'Novo Post')
@section('header_subtitle', 'Crie uma nova publicação')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-files class="lucid-icon" /> Novo Post</h2>
        <div class="top-buttons">
            <x-hook name="admin.create_post_header_buttons" :params="['post' => $post]" desc="Botões no header da criação de post" />
            <a href="{{ route('admin.posts.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
            </a>
        </div>
    </div>

    @include('admin.posts._form', [
        'action' => route('admin.posts.store')
    ])
</div>

{{-- Modais --}}
<x-media.grid-modal />
<x-media.upload-modal id="mainUploader" folder="uploads" accept="image/*,application/pdf" :max-size="10240" />
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/page-media.css') }}">
@endpush

@push('scripts')
<script>
    window.LUNAR_SHORTCODES = @json(\App\Helpers\ContentHelper::getRegisteredShortcodes());
</script>
@endpush
