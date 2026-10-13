@extends('admin.layout')

@section('header_title', 'Nova Página')
@section('header_subtitle', 'Crie uma nova página pública')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-file-text class="lucid-icon" /> Nova Página</h2>
        <div class="top-buttons">
            <x-hook name="admin.create_page_header_buttons" :params="['page' => $page]" desc="Botões no header da criação de página" />

            <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
            </a>
        </div>
    </div>

    @include('admin.pages._form', [
        'action' => route('admin.pages.store')
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
