@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Admin-dashboard.css') }}">
@endpush
@section('content')

    <div class="container mt-4">
        <div class="mb-5 text-center text-md-start">
            <h2 style="font-family: 'Playfair Display', serif; font-weight: 600; color: #0a0a0a; margin-bottom: 4px;">
                👋 Welcome, <span style="color: var(--primary-color);">{{ Auth::user()->name }}</span>
            </h2>
            <p style="color: #666; margin-top: 15px; font-size: 0.95rem; gap: 12px; flex-wrap: wrap;"
                class="d-flex align-items-center justify-content-center justify-content-md-start text-center text-md-start">
                <span
                    style="font-family: 'Outfit', sans-serif; font-size: 0.68rem; color: #0d9488; font-weight: 700; text-transform: none; letter-spacing: 0.3px; background: rgba(13, 148, 136, 0.08); padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(13, 148, 136, 0.2); box-shadow: 0 2px 8px rgba(13, 148, 136, 0.05); white-space: nowrap;">
                    <span style="margin-right: 4px;">🛡️</span> Admin
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
                            <div class="royal-card-title">Open Tickets</div>
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
                            <div class="royal-card-title">In Progress</div>
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
                            <div class="royal-card-title">Closed Tickets</div>
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
                            <div class="royal-card-title">Total Tickets</div>
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
            <!-- Unified Analytics Distribution -->
            <div class="col-12 col-lg-5">
                <div class="analysis-card">
                    <div class="analysis-header">
                        <div class="analysis-title">
                            <i class="fa-solid fa-chart-pie me-2"></i> Global Distribution
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-12 col-md-7">
                            <div class="chart-wrapper" style="height: 220px; position: relative;">
                                <div class="stat-center">
                                    <div class="stat-center-value">{{ $allOpen + $allInProgress + $allClosed }}</div>
                                    <div class="stat-center-label">Total</div>
                                </div>
                                <canvas id="distributionChart"></canvas>
                            </div>
                            <!-- Custom Legend -->
                            <div class="d-flex justify-content-center gap-3 mt-3" style="font-size: 0.75rem; font-family: 'Outfit', sans-serif;">
                                <div class="d-flex align-items-center gap-1"><span style="width: 8px; height: 8px; border-radius: 50%; background: #dc3545;"></span> Open</div>
                                <div class="d-flex align-items-center gap-1"><span style="width: 8px; height: 8px; border-radius: 50%; background: #d4af53;"></span> In Progress</div>
                                <div class="d-flex align-items-center gap-1"><span style="width: 8px; height: 8px; border-radius: 50%; background: #198754;"></span> Closed</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-5 mt-4 mt-md-0">
                            <div class="sender-analysis-mini">
                                <div class="d-flex justify-content-between mb-2">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: #555;">Source</span>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.75rem;">
                                        <span class="text-muted"><i class="fa-solid fa-user-cog me-1"></i> Agents</span>
                                        <span class="fw-bold" id="mini-agent-count">{{ $agentTicketsCount }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px; background: rgba(0,0,0,0.03); border-radius: 10px; overflow: visible;">
                                        <div class="progress-bar" id="mini-agent-bar" style="width: {{ $agentTicketsCount + $userTicketsCount > 0 ? ($agentTicketsCount / ($agentTicketsCount + $userTicketsCount)) * 100 : 0 }}%; background: #3b6fd4; border-radius: 10px; position: relative;">
                                            <div style="position: absolute; right: 0; top: -15px; font-size: 0.65rem; color: #3b6fd4; font-weight: 700;">{{ $agentTicketsCount + $userTicketsCount > 0 ? round(($agentTicketsCount / ($agentTicketsCount + $userTicketsCount)) * 100) : 0 }}%</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.75rem;">
                                        <span class="text-muted"><i class="fa-solid fa-users me-1"></i> Users</span>
                                        <span class="fw-bold" id="mini-user-count">{{ $userTicketsCount }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px; background: rgba(0,0,0,0.03); border-radius: 10px; overflow: visible;">
                                        <div class="progress-bar" id="mini-user-bar" style="width: {{ $agentTicketsCount + $userTicketsCount > 0 ? ($userTicketsCount / ($agentTicketsCount + $userTicketsCount)) * 100 : 0 }}%; background: #C9991A; border-radius: 10px; position: relative;">
                                            <div style="position: absolute; right: 0; top: -15px; font-size: 0.65rem; color: #C9991A; font-weight: 700;">{{ $agentTicketsCount + $userTicketsCount > 0 ? round(($userTicketsCount / ($agentTicketsCount + $userTicketsCount)) * 100) : 0 }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Trend -->
            <div class="col-12 col-lg-7">
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
        window.AdminDashboardConfig = {
            stats: {
                allOpen: {{ $allOpen }},
                allInProgress: {{ $allInProgress }},
                allClosed: {{ $allClosed }},
                agentCount: {{ $agentTicketsCount }},
                userCount: {{ $userTicketsCount }}
            },
            chartData: {
                labels: @json($chartLabels),
                open: @json($chartOpen),
                inProgress: @json($chartInProgress),
                closed: @json($chartClosed)
            },
            routes: {
                newData: '{{ route("admin.tickets.new-data") }}'
            },
            currentSelection: {
                date: '{{ request("date") }}',
                month: {{ $month }},
                year: {{ $year }}
                            },
            currentDate: {
                month: {{ now()->month }},
                year: {{ now()->year }}
                            }
        };
    </script>
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>

@endsection