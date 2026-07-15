@extends('public.site-layout')

@section('title', 'Documentação Técnica de Arquitetura — Lunar Base')
@section('meta_description', 'Guia de referência detalhado sobre o ecossistema, helpers, traits e configurações do Lunar Base Starter Kit.')

@section('content')
<div class="lunar-logo">
    <img src="{{ asset('images/lunar-base.png') }}" alt="Lunar Base - Laravel Starter Kit">
</div>

<div class="lunar-doc-container">

    <header class="lunar-doc-header">
        {{-- <h1 class="lunar-doc-title">Lunar Base</h1> --}}
        <p class="lunar-doc-lead">
            <strong>Lunar Base</strong> é um Starter Kit para Laravel, com
            jeito de CMS.
        </p>
    </header>


@endsection

@push('styles')
<style>
.lunar-logo {
    text-align: center;
    width: 400px;
    margin: auto;
    border: 10px solid #333;
    border-radius: 50px;
    overflow: hidden;
}

.lunar-logo img {
    max-width: 100%;
    object-fit: cover;
    display: block;
}

.lunar-doc-container {
    text-align: center;
    max-width: 900px;
    margin: 0 auto;
    /* padding: 50px 20px 0; */
}
</style>
@endpush
