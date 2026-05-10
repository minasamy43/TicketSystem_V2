@extends('layouts.app')

@section('title', 'Ranking')
@section('breadcrumb', 'Ranking')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Admin-ranking-index.css') }}">
@endpush

@section('content')
    <div class="premium-container container-fluid">
        <div class="row align-items-end mb-5">
            <div class="col-lg-7 text-center text-lg-start mb-4 mb-lg-0">
                <h1 class="page-title">Performance Elite</h1>
                <p class="text-muted lead mb-lg-0">Recognizing excellence in service and ticket resolution.</p>
            </div>
            <div class="col-lg-5">
                <form action="{{ route('admin.ranking.index') }}" method="GET"
                    class="filter-section d-flex flex-wrap flex-lg-nowrap gap-2 gap-lg-3 align-items-end mb-0">
                    <div class="flex-grow-1 flex-lg-grow-0" style="min-width: 60px;">
                        <label class="form-label small fw-bold text-muted mb-2 text-start d-block"> Day</label>
                        <select name="day" class="form-select form-select-premium shadow-none">
                            <option value="all" {{ $day == 'all' || !$day ? 'selected' : '' }}>All</option>
                            @foreach(range(1, 31) as $d)
                                <option value="{{ $d }}" {{ $day == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-grow-1 flex-lg-grow-0" style="min-width: 60px;">
                        <label class="form-label small fw-bold text-muted mb-2 text-start d-block"> Month</label>
                        <select name="month" class="form-select form-select-premium shadow-none">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-grow-1 flex-lg-grow-0" style="min-width: 80px;">
                        <label class="form-label small fw-bold text-muted mb-2 text-start d-block"> Year</label>
                        <select name="year" class="form-select form-select-premium shadow-none">
                            @foreach(range(now()->year - 2, now()->year) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold-action shadow-none w-100 w-lg-auto mt-2 mt-lg-0">
                        Show
                    </button>
                </form>
            </div>
        </div>

        @if($rankings->isEmpty())
            <div class="premium-card empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                    stroke-linecap="round" stroke-linejoin="round" class="mb-4">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h3>No data available</h3>
                <p>We couldn't find any closed tickets for the selected period.</p>
            </div>
        @else
            <div class="row">
                <div class="col-xl-6 mb-4">
                    <div class="premium-card h-100">
                        <h4 class="mb-4 fw-bold">Executive Ranking</h4>
                        <div class="ranking-list">
                            @php
                                $maxTickets = $rankings->max('tickets_count');
                                $currentRank = 1;
                                $prevTickets = -1;
                            @endphp
                            @foreach($rankings as $index => $ranking)
                                @php
                                    if ($prevTickets != -1 && $ranking->tickets_count < $prevTickets) {
                                        $currentRank = $index + 1;
                                    }
                                    $prevTickets = $ranking->tickets_count;
                                @endphp
                                <div class="top-performer-card {{ $ranking->tickets_count == $maxTickets ? 'leader' : '' }}"
                                    style="animation: slideIn {{ 0.3 + ($index * 0.1) }}s ease forwards;">
                                    <div class="rank-indicator">
                                        {{ $currentRank }}
                                    </div>
                                    <div class="performer-info">
                                        <h4 class="d-flex align-items-center">
                                            {{ $ranking->closer->name ?? 'User' }}
                                        </h4>
                                    </div>
                                    <div class="ticket-count-badge">
                                        {{ $ranking->tickets_count }} <span
                                            class="small font-weight-normal opacity-75">Solved</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 mb-4">
                    <div class="premium-card h-100">
                        <h4 class="mb-2 fw-bold">Performance Analytics</h4>
                        <p class="text-muted small mb-4">Visual distribution of monthly resolutions.</p>
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(!$rankings->isEmpty())
                const ctx = document.getElementById('performanceChart').getContext('2d');

                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, '{{ \App\Models\Setting::get('primary_color', '#d4af53') }}66');
                gradient.addColorStop(1, '{{ \App\Models\Setting::get('primary_color', '#d4af53') }}0d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($rankings->map(fn($r) => $r->closer->name ?? 'Unknown')),
                        datasets: [{
                            label: 'Tickets Resolved',
                            data: @json($rankings->pluck('tickets_count')),
                            backgroundColor: gradient,
                            borderColor: '{{ \App\Models\Setting::get('primary_color', '#d4af53') }}cc',
                            borderWidth: 2,
                            borderRadius: 15,
                            borderSkipped: false,
                            barPercentage: 0.7,
                            categoryPercentage: 0.7,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: '#000',
                                bodyColor: '#666',
                                borderColor: '{{ \App\Models\Setting::get('primary_color', '#d4af53') }}33',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                displayColors: false
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#666', font: { weight: '600' } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.03)' },
                                ticks: { color: '#999', stepSize: 1 }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
@endpush