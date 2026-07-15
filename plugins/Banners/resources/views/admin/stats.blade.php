@extends('admin.layout')
@section('header_title', 'Estatisticas do Banner')
@section('header_subtitle', 'Cliques e desempenho: ' . $banner->title)

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/banners/css/banners.css') }}">
@endpush
@endonce

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-square-activity class="lucid-icon" /> Estatisticas</h2>
        <a href="{{ route('admin.banners.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    {{-- Cards de Resumo --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><x-lucide-mouse-pointer-click class="lucid-icon" /></div>
            <div class="stat-info">
                <span class="stat-value">{{ number_format($totalClicks) }}</span>
                <span class="stat-label">Total de Cliques</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><x-lucide-calendar class="lucid-icon" /></div>
            <div class="stat-info">
                <span class="stat-value">{{ number_format($todayClicks) }}</span>
                <span class="stat-label">Hoje</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><x-lucide-calendar-days class="lucid-icon" /></div>
            <div class="stat-info">
                <span class="stat-value">{{ number_format($weekClicks) }}</span>
                <span class="stat-label">Esta Semana</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><x-lucide-calendar-range class="lucid-icon" /></div>
            <div class="stat-info">
                <span class="stat-value">{{ number_format($monthClicks) }}</span>
                <span class="stat-label">Este Mes</span>
            </div>
        </div>
    </div>

    {{-- Grafico de Cliques Diarios --}}
    <div class="admin-card" style="margin-top: 1.5rem;">
        <div class="admin-card-header">
            <h3><x-lucide-trending-up class="lucid-icon" /> Cliques por Dia</h3>
        </div>
        <div class="chart-container">
            @if(count($dailyStats) > 0)
                <div class="bar-chart" id="dailyChart">
                    @foreach($dailyStats as $period => $total)
                        <div class="bar-item">
                            <div class="bar-wrapper">
                                @php
                                    $maxVal = max($dailyStats);
                                    $height = $maxVal > 0 ? ($total / $maxVal) * 100 : 0;
                                @endphp
                                <div class="bar" style="height: {{ $height }}%;">
                                    <span class="bar-value">{{ $total }}</span>
                                </div>
                            </div>
                            <span class="bar-label">{{ $period }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="admin-text-muted admin-text-center" style="padding: 3rem;">
                    Nenhum clique registrado ainda.
                </p>
            @endif
        </div>
    </div>

    {{-- Tabela de Cliques Recentes --}}
    <div class="admin-card" style="margin-top: 1.5rem;">
        <div class="admin-card-header">
            <h3><x-lucide-list class="lucid-icon" /> Cliques Recentes</h3>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>IP</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentClicks as $click)
                    <tr>
                        <td>{{ $click->clicked_at->format('d/m/Y H:i:s') }}</td>
                        <td><code>{{ $click->ip_address ?? 'N/A' }}</code></td>
                        <td class="user-agent-cell">{{ Str::limit($click->user_agent ?? 'N/A', 80) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="admin-text-center admin-text-muted">Nenhum clique registrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
