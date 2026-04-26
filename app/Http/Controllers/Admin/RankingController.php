<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RankingController extends Controller
{
    public function index()
    {
        $month = request('month', Carbon::now()->month);
        $year = request('year', Carbon::now()->year);
        $day = request('day');

        $query = Ticket::where('status', 'closed')
            ->whereNotNull('closed_by');

        if ($day && $day !== 'all') {
            $startDate = Carbon::createFromDate($year, $month, $day)->startOfDay();
            $endDate = Carbon::createFromDate($year, $month, $day)->endOfDay();
        } else {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        }

        $rankings = $query->whereBetween('updated_at', [$startDate, $endDate])
            ->select('closed_by', DB::raw('count(*) as tickets_count'))
            ->groupBy('closed_by')
            ->with('closer:id,name')
            ->orderBy('tickets_count', 'desc')
            ->get();

        return view('admin.ranking.index', compact('rankings', 'month', 'year', 'day'));
    }

}
