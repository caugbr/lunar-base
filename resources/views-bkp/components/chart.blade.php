@props([
    'id' => 'chart-' . uniqid(),
    'labels' => [],
    'data' => [],
    'label' => 'Dados',
    'type' => 'bar',
    'height' => 300,
    'colors' => null,
])

<div class="chart-container" style="height: {{ $height }}px; width: 100%;">
    <canvas id="{{ $id }}"></canvas>
</div>

@push('scripts')
    {{-- Carrega o script do Chart.js UMA ÚNICA VEZ por página, independente de quantos gráficos existirem --}}
    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    <script>
        (function() {
            let chartInstance = null;
            const canvasId = '{{ $id }}';
            const chartType = '{{ $type }}';
            const chartLabels = @json($labels);
            const chartData = @json($data);
            const chartLabel = '{{ $label }}';
            const customColors = @json($colors);

            const isPie = chartType === 'pie' || chartType === 'doughnut';

            // Cores padrão para gráficos de pizza/rosca
            const defaultColors = [
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b',
                '#8b5cf6', '#ec489a', '#06b6d4', '#84cc16'
            ];

            function initChart() {
                const ctx = document.getElementById(canvasId)?.getContext('2d');
                if (!ctx) return;

                if (chartInstance) {
                    chartInstance.destroy();
                }

                chartInstance = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: chartLabel,
                            data: chartData,
                            backgroundColor: isPie
                                ? (customColors || defaultColors)
                                : 'rgba(37, 99, 235, 0.7)',
                            borderColor: isPie ? '#fff' : 'rgb(37, 99, 235)',
                            borderWidth: isPie ? 2 : 1,
                            borderRadius: isPie ? 0 : 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: isPie,
                        plugins: {
                            legend: { position: isPie ? 'bottom' : 'top' }
                        }
                    }
                });
            }

            // Inicializa após o DOM estar pronto
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                initChart();
            } else {
                document.addEventListener('DOMContentLoaded', initChart);
            }

            // Helper de debounce para redimensionamento
            function debounce(func, wait = 150) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            window.addEventListener('resize', debounce(function() {
                if (chartInstance) {
                    chartInstance.resize();
                }
            }));
        })();
    </script>
@endpush
