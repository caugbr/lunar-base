@extends('admin.layout')
@section('header_title', 'Biblioteca de Mídia')
@section('header_subtitle', 'Gerencie imagens, documentos e arquivos')

@section('content')
<div class="admin-card" x-data>
    <div class="admin-card-header">
        <h2><x-lucide-image class="lucid-icon" /> Biblioteca de Mídia</h2>
        <div class="top-buttons">
            <button @click="$dispatch('media:upload-open', { id: 'mainUploader' })" class="admin-btn admin-btn-primary">
                <x-lucide-upload class="lucid-icon" /> <span>Upload</span>
            </button>
            {{-- <button @click="window.dispatchEvent(new CustomEvent('media:upload-open', { detail: { id: 'mainUploader' } }))" class="admin-btn admin-btn-primary">
                <x-lucide-upload class="lucid-icon" /> <span>Upload</span>
            </button> --}}
        </div>
    </div>

    {{-- Grid como componente reutilizável --}}
    <x-media.grid
        id="mainGrid"
        :selectable="false"
        :on-select="null"
        :per-page="20"
        initial-type=""
    />
</div>

{{-- Modais (fora do card, mas na mesma view) --}}
<x-media.upload-modal
    id="mainUploader"
    folder="uploads"
    accept="image/*,application/pdf"
    :max-size="10240"
/>

<x-media.edit-modal />
@endsection
