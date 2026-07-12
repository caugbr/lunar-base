{{-- admin/dashboard/index.blade.php --}}
@extends('admin.layout')

@section('header_title', $config['title'])
@section('header_subtitle', $config['subtitle'])

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2>
            <x-dynamic-component component="lucide-{{ $config['icon'] ?? 'layout-dashboard' }}" class="lucid-icon" />
            {{ $config['cardTitle'] }}
        </h2>
    </div>
    <div class="dashboard-grid" style="grid-template-columns: repeat({{ $config['columns'] }}, 1fr)">

        @foreach($boxes as $box)
            {!! \App\Support\Dashboard::render($box['id']) !!}
        @endforeach

    </div>
</div>
@endsection
