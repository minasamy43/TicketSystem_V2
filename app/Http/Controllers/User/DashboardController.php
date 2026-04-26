<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $defaultDate = now()->format('Y-m-d');
        $date = request()->filled('date') ? request('date') : $defaultDate;

        $query = Ticket::where('user_id', $userId)
                        ->whereDate('created_at', $date)
                        ->withCount(['replies as unread_replies_count' => function($query) {
                            $query->whereNotNull('admin_id')->where('is_read', 0);
                        }]);

        if ($subject = request('subject')) {
            $query->where('subject', 'like', '%' . $subject . '%');
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($closerName = request('closer_name')) {
            $query->whereHas('closer', function($q) use ($closerName) {
                $q->where('name', 'like', '%' . $closerName . '%');
            });
        }

        $tickets = $query->latest()->paginate(10);

        return view('user.dashboard',compact(
            'tickets',
            'date'
        ));
    }
}
