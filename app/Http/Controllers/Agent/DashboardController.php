<?php

namespace App\Http\Controllers\Agent;
 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use Illuminate\Http\Request;

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

        $tickets = $query->latest()->paginate(10)->withQueryString();

        // Status cards — Today's totals (respecting date filter)
        $totalTickets = Ticket::where('user_id', $userId)->whereDate('created_at', $date)->count();
        $openTickets  = Ticket::where('user_id', $userId)->whereDate('created_at', $date)->where('status', 'open')->count();
        $closedTickets = Ticket::where('user_id', $userId)->whereDate('created_at', $date)->where('status', 'closed')->count();
        $inProgress   = Ticket::where('user_id', $userId)->whereDate('created_at', $date)->where('status', 'in progress')->count();

        return view('agent.dashboard',compact(
            'tickets',
            'date',
            'totalTickets',
            'openTickets',
            'closedTickets',
            'inProgress'
        ));
    }

    public function getNewTicketsData(Request $request)
    {
        $userId = Auth::id();
        $lastId = $request->get('last_id', 0);
        $date = $request->get('date', now()->format('Y-m-d'));
        $existingIds = $request->get('existing_ids', []); // Array of IDs currently on the page

        // 1. Fetch newly created tickets
        $query = Ticket::with(['closer'])
            ->where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->where('id', '>', $lastId)
            ->withCount(['replies as unread_replies_count' => function($query) {
                $query->whereNotNull('admin_id')->where('is_read', 0);
            }]);

        if ($subject = $request->get('subject')) {
            $query->where('subject', 'like', '%' . $subject . '%');
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($closerName = $request->get('closer_name')) {
            $query->whereHas('closer', function($q) use ($closerName) {
                $q->where('name', 'like', '%' . $closerName . '%');
            });
        }

        $newTickets = $query->latest()->get();

        // 2. Fetch updates for existing tickets
        $updates = [];
        if (!empty($existingIds)) {
            $updatedTickets = Ticket::with(['closer'])
                ->where('user_id', $userId)
                ->whereIn('id', $existingIds)
                ->withCount(['replies as unread_replies_count' => function($query) {
                    $query->whereNotNull('admin_id')->where('is_read', 0);
                }])->get();

            foreach ($updatedTickets as $t) {
                $statusColor = $t->status == 'open' ? 'rgba(220, 53, 69, 0.1)' : ($t->status == 'in progress' ? 'rgba(212, 175, 83, 0.15)' : 'rgba(25, 135, 84, 0.1)');
                $statusTextCol = $t->status == 'open' ? '#dc3545' : ($t->status == 'in progress' ? '#d4af53' : '#198754');
                $statusIcon = $t->status == 'open' ? '🎟️' : ($t->status == 'in progress' ? '👍🏻' : '✅️');

                $updates[] = [
                    'id' => $t->id,
                    'status' => $t->status,
                    'status_bg' => $statusColor,
                    'status_color' => $statusTextCol,
                    'status_icon' => $statusIcon,
                    'closer' => $t->closer->name ?? '---',
                    'unread_count' => $t->unread_replies_count,
                    'status_label' => ucfirst($t->status),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'new_tickets' => $newTickets->map(function($t) {
                $statusColor = $t->status == 'open' ? 'rgba(220, 53, 69, 0.1)' : ($t->status == 'in progress' ? 'rgba(212, 175, 83, 0.15)' : 'rgba(25, 135, 84, 0.1)');
                $statusTextCol = $t->status == 'open' ? '#dc3545' : ($t->status == 'in progress' ? '#d4af53' : '#198754');
                $statusIcon = $t->status == 'open' ? '🎟️' : ($t->status == 'in progress' ? '👍🏻' : '✅️');
                
                return [
                    'id' => $t->id,
                    'subject' => $t->subject,
                    'status' => $t->status,
                    'status_label' => ucfirst($t->status),
                    'status_bg' => $statusColor,
                    'status_color' => $statusTextCol,
                    'status_icon' => $statusIcon,
                    'closer' => $t->closer->name ?? '---',
                    'time' => $t->created_at->format('g:i A'),
                    'relative_time' => $t->created_at->diffForHumans(),
                    'unread_count' => $t->unread_replies_count,
                ];
            }),
            'updates' => $updates,
            'new_highest_id' => $newTickets->max('id') ?: $lastId
        ]);
    }
}
