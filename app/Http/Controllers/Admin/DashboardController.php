<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $defaultDate = now()->format('Y-m-d');
        $date = request()->filled('date') ? request('date') : $defaultDate;

        // Filters for Analysis
        $month = request('month', now()->month);
        $year = request('year', now()->year);

        // Status cards — Respect requested date (defaults to today)
        $totalTickets = Ticket::whereDate('created_at', $date)->count();
        $openTickets  = Ticket::whereDate('created_at', $date)->where('status', 'open')->count();
        $closedTickets = Ticket::whereDate('created_at', $date)->where('status', 'closed')->count();
        $inProgress   = Ticket::whereDate('created_at', $date)->where('status', 'in progress')->count();

        // Data for monthly analysis chart
        $chartLabels = [];
        $chartOpen = [];
        $chartInProgress = [];
        $chartClosed = [];

        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $d = \Carbon\Carbon::createFromDate($year, $month, $i)->format('Y-m-d');
            $chartLabels[] = \Carbon\Carbon::parse($d)->format('M d');
            $chartOpen[] = Ticket::whereDate('created_at', $d)->where('status', 'open')->count();
            $chartInProgress[] = Ticket::whereDate('created_at', $d)->where('status', 'in progress')->count();
            $chartClosed[] = Ticket::whereDate('created_at', $d)->where('status', 'closed')->count();
        }

        // Totals for distribution chart (respecting 'month/year' filter)
        $monthStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return view('admin.dashboard', [
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'closedTickets' => $closedTickets,
            'inProgress' => $inProgress,
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'chartLabels' => $chartLabels,
            'chartOpen' => $chartOpen,
            'chartInProgress' => $chartInProgress,
            'chartClosed' => $chartClosed,
            'allOpen' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'open')->count(),
            'allInProgress' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'in progress')->count(),
            'allClosed' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'closed')->count(),
            'agentTicketsCount' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->whereHas('user', function($q) { $q->where('role', 0); })->count(),
            'userTicketsCount' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->whereHas('user', function($q) { $q->where('role', 2); })->count(),
        ]);
    }
}
