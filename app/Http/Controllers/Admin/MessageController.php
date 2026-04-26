<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /** List messages from tickets the authenticated admin is involved in */
    public function index()
    {
        $adminId = Auth::id();

        // Get IDs of tickets this admin is involved in or that are unassigned
        $ticketIds = Ticket::where('inprogress_by', $adminId)
            ->orWhere('closed_by', $adminId)
            ->orWhereNull('inprogress_by')
            ->orWhereIn('id', function($query) use ($adminId) {
                $query->select('ticket_id')->from('replies')->where('admin_id', $adminId);
            })
            ->pluck('id');

        // Show ONLY the latest reply for each of those tickets
        $messages = Reply::whereIn('id', function($query) use ($ticketIds) {
                $query->selectRaw('max(id)')
                    ->from('replies')
                    ->whereIn('ticket_id', $ticketIds)
                    ->groupBy('ticket_id');
            })
            ->with(['ticket.user', 'user', 'admin'])
            ->latest()
            ->paginate(20);

        return view('admin.messages.index', compact('messages'));
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
            ->orWhereIn('id', function($query) use ($adminId) {
                $query->select('ticket_id')->from('replies')->where('admin_id', $adminId);
            })
            ->pluck('id');

        // Get the latest reply for each ticket that has a new reply
        $newReplies = Reply::whereIn('id', function($query) use ($ticketIds, $lastReplyId) {
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
