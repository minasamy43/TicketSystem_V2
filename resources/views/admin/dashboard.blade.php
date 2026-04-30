@extends('layouts.app')

@section('breadcrumb', 'Dashboard Overview')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
@endpush


@section('content')

    <style>
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .new-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.25em 0.6em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            letter-spacing: 0.03em;
            vertical-align: middle;
        }

        .pulse-dot {
            width: 6px;
            height: 6px;
            background-color: #dc3545;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 5px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .unread-row {
            background: linear-gradient(90deg, rgba(220, 53, 69, 0.04) 0%, transparent 30%);
        }

        .unread-row td {
            font-weight: 600 !important;
            color: #111 !important;
        }

        .new-entry-flash {
            animation: flashRow 3s ease-out;
        }

        @keyframes flashRow {
            0% {
                background-color: var(--primary-light);
            }

            100% {
                background-color: transparent;
            }
        }

        /* Royal Minimalist Cards */
        .royal-card {
            background: #ffffff;
            border-radius: 18px;
            position: relative;
            padding: 1.6rem 1.75rem 1.4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 130px;
            transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .royal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
            border-color: rgba(var(--accent-rgb, 212, 175, 83), 0.25);
        }

        /* Left accent bar */
        .royal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 4px;
            background: var(--accent-color, #d4af53);
            border-radius: 4px 0 0 4px;
        }

        /* Top edge gradient shimmer */
        .royal-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 4px;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-color, #d4af53) 0%, transparent 100%);
            opacity: 0.18;
            border-radius: 0 18px 0 0;
        }

        .royal-card-watermark {
            position: absolute;
            right: -8px;
            bottom: -8px;
            color: var(--accent-color, #d4af53);
            opacity: 0.19;
            transform: rotate(-15deg);
            z-index: 0;
        }

        .royal-card-content {
            position: relative;
            z-index: 1;
        }

        .royal-card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            color: #888;
            letter-spacing: 0.3px;
            margin-bottom: 0.55rem;
        }

        .royal-card-value {
            font-family: 'Inter', sans-serif;
            font-size: 2rem;
            font-weight: 500;
            color: #111;
            line-height: 1;
            letter-spacing: -1px;
        }

        .royal-card-sub {
            font-size: 0.72rem;
            color: #bbb;
            margin-top: 0.45rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 400;
        }

        .royal-card-icon-min {
            position: absolute;
            top: 1.4rem;
            right: 1.4rem;
            width: 52px;
            height: 52px;
            background: var(--icon-bg, rgba(212, 175, 83, 0.08));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color, #d4af53);
            font-size: 1.45rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .royal-card:hover .royal-card-icon-min {
            transform: scale(1.1) rotate(-5deg);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
        }

        /* Standard Table Overrides to match User View */
        .table-bordered {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e8eaed !important;
            box-shadow: 0 1px 15px rgba(0, 0, 0, 0.02);
        }

        .table-bordered thead th {
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
            text-transform: none;
            letter-spacing: normal;
            font-size: 0.88rem;
            padding: 12px 15px;
            border-bottom: 2px solid #e8eaed !important;
            /* border-bottom: 2px solid rgba(212, 175, 83, 0.15); */

        }

        .table-bordered tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            font-size: 0.92rem;
            border-color: #edebe8ff !important;
        }

        .table-bordered tbody tr:not(.empty-state-row):hover td {
            background: rgba(0, 0, 0, 0.02) !important;
        }

        .action-btn-premium {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #eee;
            color: var(--primary-color);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .action-btn-premium:hover {
            /* background: var(--primary-color); */
            color: #fff;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--primary-light);
        }

        /* Inline Filters */
        .inline-filter-select,
        .inline-filter-input {
            background: #fcfcfc;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 6px;
            font-size: 0.75rem;
            padding: 4px 8px;
            color: #555;
            width: 100%;
            transition: all 0.2s ease;
            font-family: 'DM Sans', sans-serif;
        }

        .inline-filter-select:focus,
        .inline-filter-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .btn-clear-inline {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #eee;
            color: #666;
            transition: all 0.3s ease;
        }

        .btn-clear-inline:hover {
            background: #f8f9fa;
            color: #dc3545;
            border-color: #ffcccc;
        }

        /* Live Status Select Badge */
        .status-select-badge {
            border: none;
            border-radius: 10px;
            padding: 0.5rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            text-align: center;
            width: auto;
            min-width: 90px;
        }

        .status-select-badge:focus {
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .status-select-badge option {
            background: #ffffff !important;
            color: #333333 !important;
        }

        .status-open {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
        }

        .status-progress {
            background: var(--primary-light) !important;
            color: var(--primary-color) !important;
        }

        .status-closed {
            background: rgba(25, 135, 84, 0.1) !important;
            color: #198754 !important;
        }

        .search-icon-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 10px center;
            padding-left: 30px !important;
        }

        /* Hide placeholder text, keep only the icon */
        .search-icon-input::placeholder {
            color: transparent;
        }

        /* Mobile Responsiveness */
        @media (max-width: 991px) {
            .royal-card {
                min-height: 110px;
                padding: 1.25rem;
            }

            .royal-card-value {
                font-size: 1.6rem;
            }

            .royal-card-icon-min {
                width: 42px;
                height: 42px;
                font-size: 1.1rem;
                top: 1rem;
                right: 1rem;
            }
        }

        @media (max-width: 768px) {

            .table-bordered thead th,
            .table-bordered tbody td {
                padding: 10px;
                font-size: 0.8rem;
            }

            .status-select-badge {
                min-width: 80px;
                padding: 0.4rem 0.6rem;
                font-size: 0.7rem;
            }

            .action-btn-premium {
                width: 32px;
                height: 32px;
            }
        }

        /* Analysis Chart Section */
        .analysis-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 1.8rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.03);
            height: 100%;
            transition: all 0.3s ease;
        }

        .analysis-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }

        .analysis-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .analysis-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #111;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .analysis-title i,
        .analysis-title svg {
            color: var(--primary-color);
        }

        .chart-wrapper {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .stat-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        .stat-center-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #111;
            line-height: 1;
            font-family: 'Inter', sans-serif;
        }

        .stat-center-label {
            font-size: 0.65rem;
            color: #888;
            letter-spacing: 1px;
            font-family: 'Outfit', sans-serif;
            margin-top: 4px;
        }

        @media (max-width: 991px) {
            .chart-wrapper {
                height: 250px;
            }
        }
    </style>

    <div class="container mt-4">

        <div class="mb-5 text-center text-md-start">
            <h2 style="font-family: 'Playfair Display', serif; font-weight: 600; color: #0a0a0a; margin-bottom: 4px;">
                👋 Welcome, <span style="color: var(--primary-color);">{{ Auth::user()->name }}</span>
            </h2>
            <p style="color: #666; margin-top: 15px; font-size: 0.95rem; gap: 12px; flex-wrap: wrap;"
                class="d-flex align-items-center justify-content-center justify-content-md-start text-center text-md-start">
                <span
                    style="font-family: 'Outfit', sans-serif; font-size: 0.68rem; color: #0d9488; font-weight: 700; text-transform: none; letter-spacing: 0.3px; background: rgba(13, 148, 136, 0.08); padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(13, 148, 136, 0.2); box-shadow: 0 2px 8px rgba(13, 148, 136, 0.05); white-space: nowrap;">
                    <span style="margin-right: 4px;">🛡️</span> Technical
                </span>
                <span>Monitor tickets, manage users, and view system analytics below.</span>
            </p>
        </div>

        <div class="row g-4 mb-5">

            <!-- Open Tickets -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}"
                    style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #dc3545; --icon-bg: rgba(220, 53, 69, 0.08);">
                        <div class="royal-card-watermark">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M2 9V5.2a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2V9a2 2 0 0 0 0 6v3.8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V15a2 2 0 0 0 0-6z" />
                                <path d="M14 3v2" />
                                <path d="M14 8v2" />
                                <path d="M14 13v2" />
                                <path d="M14 18v2" />
                            </svg>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">Today's Open</div>
                            <div class="royal-card-value" id="open-count">{{ $openTickets }}</div>
                            <div class="royal-card-sub">New today</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- In Progress -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('admin.tickets.index', ['status' => 'in progress']) }}"
                    style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #d4af53; --icon-bg: rgba(212, 175, 83, 0.08);">
                        <div class="royal-card-watermark">
                            <span style="font-size: 55px;">👍🏻</span>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">Daily Progress</div>
                            <div class="royal-card-value" id="progress-count">{{ $inProgress }}</div>
                            <div class="royal-card-sub">Handled today</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Closed Tickets -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('admin.tickets.index', ['status' => 'closed']) }}"
                    style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #198754; --icon-bg: rgba(25, 135, 84, 0.08);">
                        <div class="royal-card-watermark">
                            <span style="font-size: 55px;">✅️</span>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">Daily Closed</div>
                            <div class="royal-card-value" id="closed-count">{{ $closedTickets }}</div>
                            <div class="royal-card-sub">Resolved today</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Tickets -->
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('admin.tickets.index') }}" style="text-decoration: none; color: inherit;">
                    <div class="royal-card" style="--accent-color: #0d6efd; --icon-bg: rgba(13, 110, 253, 0.08);">
                        <div class="royal-card-watermark">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                <path d="M12 11h4"></path>
                                <path d="M12 16h4"></path>
                                <path d="M8 11h.01"></path>
                                <path d="M8 16h.01"></path>
                            </svg>
                        </div>

                        <div class="royal-card-content">
                            <div class="royal-card-title">Today's Total</div>
                            <div class="royal-card-value" id="total-count">{{ $totalTickets }}</div>
                            <div class="royal-card-sub">Requests today</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Analysis Header & Filters -->
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-4 mb-4">
            <div>
                <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #111; margin: 0;">Performance
                    Analytics</h3>
                <p style="color: #888; font-size: 0.85rem; margin: 5px 0 0 0;">Visual distribution and historical trends.
                </p>
            </div>

            <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex align-items-center gap-2 p-2"
                style="background: #fff; border-radius: 12px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                <div style="min-width: 120px;">
                    <select name="month" class="form-select border-0 shadow-none"
                        style="font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 500; cursor: pointer;"
                        onchange="this.form.submit()">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 90px; border-left: 1px solid #eee;">
                    <select name="year" class="form-select border-0 shadow-none"
                        style="font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 500; cursor: pointer;"
                        onchange="this.form.submit()">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request()->filled('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
            </form>
        </div>

        <div class="row g-4 mb-5">
            <!-- Status Distribution -->
            <div class="col-12 col-lg-4">
                <div class="analysis-card">
                    <div class="analysis-header">
                        <div class="analysis-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                            </svg>
                            Distribution by Month
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <div class="stat-center">
                            <div class="stat-center-value">{{ $allOpen + $allInProgress + $allClosed }}</div>
                            <div class="stat-center-label">Total</div>
                        </div>
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Weekly Trend -->
            <div class="col-12 col-lg-8">
                <div class="analysis-card">
                    <div class="analysis-header">
                        <div class="analysis-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3v18h18"></path>
                                <path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
                            </svg>
                            Ticket Flow
                        </div>
                        <div
                            style="font-size: 0.75rem; color: #888; font-family: 'Outfit', sans-serif; background: rgba(0,0,0,0.03); padding: 4px 10px; border-radius: 50px;">
                            {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials._chat')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Shared config
            const fonts = { family: "'Outfit', sans-serif" };

            // 1. Distribution Chart
            const distCtx = document.getElementById('distributionChart').getContext('2d');
            new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Open', 'In Progress', 'Closed'],
                    datasets: [{
                        data: [{{ $allOpen }}, {{ $allInProgress }}, {{ $allClosed }}],
                        backgroundColor: ['#dc3545', '{{ \App\Models\Setting::get('primary_color', '#d4af53') }}', '#198754'],
                        borderWidth: 0,
                        hoverOffset: 15
                    }]
                },
                options: {
                    cutout: '80%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: { size: 11, ...fonts }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#111',
                            bodyColor: '#666',
                            borderColor: 'rgba(0,0,0,0.05)',
                            borderWidth: 1,
                            padding: 12,
                            usePointStyle: true,
                            callbacks: {
                                label: (ctx) => ` ${ctx.label}: ${ctx.raw} Tickets`
                            }
                        }
                    }
                }
            });

            // 2. Trend Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');

            // Create gradients
            const gradOpen = trendCtx.createLinearGradient(0, 0, 0, 300);
            gradOpen.addColorStop(0, 'rgba(220, 53, 69, 0.15)');
            gradOpen.addColorStop(1, 'rgba(220, 53, 69, 0)');

            const gradProgress = trendCtx.createLinearGradient(0, 0, 0, 300);
            gradProgress.addColorStop(0, 'rgba(212, 175, 83, 0.15)');
            gradProgress.addColorStop(1, 'rgba(212, 175, 83, 0)');

            const gradClosed = trendCtx.createLinearGradient(0, 0, 0, 300);
            gradClosed.addColorStop(0, 'rgba(25, 135, 84, 0.15)');
            gradClosed.addColorStop(1, 'rgba(25, 135, 84, 0)');

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Open',
                            data: @json($chartOpen),
                            borderColor: '#dc3545',
                            backgroundColor: gradOpen,
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#dc3545',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        },
                        {
                            label: 'In Progress',
                            data: @json($chartInProgress),
                            borderColor: '{{ \App\Models\Setting::get('primary_color', '#d4af53') }}',
                            backgroundColor: gradProgress,
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '{{ \App\Models\Setting::get('primary_color', '#d4af53') }}',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        },
                        {
                            label: 'Closed',
                            data: @json($chartClosed),
                            borderColor: '#198754',
                            backgroundColor: gradClosed,
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#198754',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#111',
                            bodyColor: '#666',
                            borderColor: 'rgba(0,0,0,0.05)',
                            borderWidth: 1,
                            padding: 12,
                            usePointStyle: true,
                            font: fonts
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fonts, color: '#999' } },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: fonts, color: '#999' },
                            grid: { borderDash: [5, 5], color: 'rgba(0,0,0,0.03)' }
                        }
                    }
                }
            });
        });

        // Real-time Dashboard Summary Discovery
        setInterval(async () => {
            try {
                const url = new URL('{{ route("admin.tickets.new-data") }}', window.location.origin);
                url.searchParams.set('date', '{{ $date }}');

                const response = await fetch(url);
                const data = await response.json();

                if (data.success && data.counts) {
                    const counts = data.counts;
                    updateValueWithEffect('open-count', counts.open);
                    updateValueWithEffect('progress-count', counts.in_progress);
                    updateValueWithEffect('closed-count', counts.closed);
                    updateValueWithEffect('total-count', counts.total);
                }
            } catch (error) {
                console.error('Error fetching summary data:', error);
            }
        }, 10000);

        function updateValueWithEffect(id, newValue) {
            const el = document.getElementById(id);
            if (!el) return;
            const currentVal = parseInt(el.textContent);
            if (currentVal !== newValue) {
                el.style.transition = 'all 0.3s ease';
                el.style.transform = 'scale(1.2)';
                el.style.color = '#d4af53';
                setTimeout(() => {
                    el.textContent = newValue;
                    el.style.transform = 'scale(1)';
                    el.style.color = '';
                }, 300);
            }
        }
    </script>

@endsection