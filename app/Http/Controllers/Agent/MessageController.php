<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $filter = $request->get('filter', 'today');
        $date = $request->get('date', now()->format('Y-m-d'));

        // User only sees messages from their own tickets
        $ticketIds = Ticket::where('user_id', $userId)->pluck('id');

        // Build the sub-query that picks the max reply per ticket
        $latestIdsQuery = function ($query) use ($ticketIds) {
            $query->selectRaw('max(id)')
                ->from('replies')
                ->whereIn('ticket_id', $ticketIds)
                ->groupBy('ticket_id');
        };

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

        return view('agent.messages.index', compact('messages', 'filter', 'date'));
    }

    public function getNewMessagesData(Request $request)
    {
        $userId = Auth::id();
        $lastReplyId = $request->get('last_reply_id', 0);
        $ticketIds = Ticket::where('user_id', $userId)->pluck('id');

        $date = $request->get('date', now()->format('Y-m-d'));
        $filter = $request->get('filter', 'today');

        $query = Reply::whereIn('id', function ($query) use ($ticketIds, $lastReplyId) {
            $query->selectRaw('max(id)')
                ->from('replies')
                ->whereIn('ticket_id', $ticketIds)
                ->where('id', '>', $lastReplyId)
                ->groupBy('ticket_id');
        });

        switch ($filter) {
            case 'today': $query->whereDate('created_at', today()); break;
            case 'yesterday': $query->whereDate('created_at', today()->subDay()); break;
            case 'week': $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]); break;
            case 'month': $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]); break;
            case 'custom': if ($date) { $query->whereDate('created_at', $date); } break;
        }

        $newReplies = $query->with(['ticket.user', 'user', 'admin'])->latest()->get();

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
