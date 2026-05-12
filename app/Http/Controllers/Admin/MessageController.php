<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $adminId = Auth::id();
        $filter = $request->get('filter', 'all');   // all|today|yesterday|week|month|custom
        $date = $request->get('date');             // YYYY-MM-DD for custom filter

        // Get IDs of tickets this admin is involved in or that are unassigned
        $ticketIds = Ticket::where('inprogress_by', $adminId)
            ->orWhere('closed_by', $adminId)
            ->orWhereNull('inprogress_by')
            ->orWhereIn('id', function ($query) use ($adminId) {
                $query->select('ticket_id')->from('replies')->where('admin_id', $adminId);
            })
            ->pluck('id');

        // Build the sub-query that picks the max reply per ticket
        $latestIdsQuery = function ($query) use ($ticketIds) {
            $query->selectRaw('max(id)')
                ->from('replies')
                ->whereIn('ticket_id', $ticketIds)
                ->groupBy('ticket_id');
        };

        // Apply date filter on the outer query
        $messagesQuery = Reply::whereIn('id', $latestIdsQuery)
            ->with(['ticket.user', 'user', 'admin']);

        switch ($filter) {
            case 'today':
                $messagesQuery->whereDate('created_at', today());
                break;
            case 'yesterday':
                $messagesQuery->whereDate('created_at', today()->subDay());
                break;
            case 'week':
                $messagesQuery->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
                break;
            case 'month':
                $messagesQuery->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
                break;
            case 'custom':
                if ($date) {
                    $messagesQuery->whereDate('created_at', $date);
                }
                break;
        }

        $messages = $messagesQuery->latest()->paginate(20)->withQueryString();

        return view('admin.messages.index', compact('messages', 'filter', 'date'));
    }

    /** Get new messages data for real-time updates. */
    public function getNewMessagesData(Request $request)
    {
        $adminId = Auth::id();
        $lastReplyId = $request->get('last_reply_id', 0);

        // Visibility logic (must match index)
        $ticketIds = Ticket::where('inprogress_by', $adminId)
            ->orWhere('closed_by', $adminId)
            ->orWhereNull('inprogress_by')
            ->orWhereIn('id', function ($query) use ($adminId) {
                $query->select('ticket_id')->from('replies')->where('admin_id', $adminId);
            })
            ->pluck('id');

        // Get the latest reply for each ticket that has a new reply
        $newReplies = Reply::whereIn('id', function ($query) use ($ticketIds, $lastReplyId) {
            $query->selectRaw('max(id)')
                ->from('replies')
                ->whereIn('ticket_id', $ticketIds)
                ->where('id', '>', $lastReplyId)
                ->groupBy('ticket_id');
        })
            ->with(['ticket.user', 'user', 'admin'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'new_messages' => $newReplies->map(function ($m) {
                return [
                    'id' => $m->id,
                    'ticket_id' => $m->ticket_id,
                    'user_name' => $m->ticket->user->name ?? 'Unknown User',
                    'body' => mb_strimwidth($m->body, 0, 100, "..."),
                    'image' => $m->image,
                    'is_read' => $m->is_read,
                    'is_from_admin' => $m->isFromAdmin(),
                    'relative_time' => $m->created_at->diffForHumans(),
                ];
            }),
            'new_highest_id' => max($newReplies->pluck('id')->toArray() + [$lastReplyId])
        ]);
    }
}
