@extends('admin.layout')

@section('header_title', 'Editar Página')
@section('header_subtitle', 'Modifique o conteúdo da página')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-file-text class="lucid-icon" /> Editar: {{ $page->title }}</h2>
        <div class="top-buttons">
            <x-hook name="admin.edit_page_header_buttons" :params="['page' => $page]" desc="Botões no header da edição de página" />
            <a href="{{ $page->url }}" class="admin-btn admin-btn-secondary" target="_blank">
                <x-lucide-external-link class="lucid-icon" /> <span>Visitar</span>
            </a>
            <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
            </a>
        </div>
    </div>

    @include('admin.pages._form', [
        'action' => route('admin.pages.update', $page->id)
    ])
</div>

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
