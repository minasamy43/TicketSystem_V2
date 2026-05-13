<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{

    public function show($id)
    {
        $ticket = Ticket::with([
            'user',
            'replies' => function ($q) {
                $q->with('admin', 'user')->oldest();
            }
        ])->withCount([
                    'replies as unread_replies_count' => function ($query) {
                        $query->whereNotNull('admin_id')->where('is_read', 0);
                    }
                ])->findOrFail($id);

        if ($ticket->user_id !== Auth::id() && Auth::user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        if (Auth::user()->role == 1) {
            return view('admin.show-ticket', compact('ticket'));
        }
        return view('agent.show-ticket', compact('ticket'));
    }

    public function create()
    {
        return view('agent.create-ticket');
    }
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|max:64',
            'message' => 'required|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'subject.required' => 'The subject is required and cannot be empty.',
            'subject.max' => 'The subject may not be greater than 64 characters.',
            'message.required' => 'The message is required and cannot be empty.',
            'message.max' => 'The message may not be greater than 1000 characters.',
            'images.*.image' => 'The file must be an image.',
            'images.*.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'images.*.max' => 'The image may not be greater than 2048 kilobytes.',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '_' . $index . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('tickets', $filename, 'public');
                $imagePaths[] = $path;
            }
        }

        Ticket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
            'images' => $imagePaths
        ]);

        return redirect()->route('agent.dashboard')->with('success', 'Ticket created successfully');
    }


    public function reply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'body' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if (!$request->body && !$request->hasFile('image')) {
            return back()->with('error', 'Message or image is required.');
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('tickets', $filename, 'public');
        }

        $reply = \App\Models\Reply::create([
            'ticket_id' => $id,
            'user_id' => Auth::id(),
            'admin_id' => null,
            'body' => $request->body ?? '',
            'image' => $imagePath,
        ]);

        // Mark as unread for the admin
        Ticket::where('id', $id)->update(['has_admin_read' => false]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply sent.',
                'reply' => [
                    'id' => $reply->id,
                    'body' => $reply->body,
                    'is_admin' => false,
                    'image' => $reply->image ? asset('storage/' . $reply->image) : null,
                    'sender' => 'You',
                    'time' => $reply->created_at->format('g:i A'),
                ]
            ]);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function close(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $ticket->update([
            'status' => 'closed',
            'has_admin_read' => false
        ]);

        return back()->with('success', 'Ticket closed successfully.');
    }

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $ticket->delete();

        return redirect()->route('agent.dashboard')->with('success', 'Ticket deleted successfully');
    }

    /** Get ticket chat data for AJAX popup. */
    public function getChatData(Request $request, $id)
    {
        $ticket = Ticket::with(['user', 'replies.admin'])->findOrFail($id);

        if ($ticket->user_id !== Auth::id() && Auth::user()->role != 1) {
            abort(403, 'Unauthorized');
        }

        $lastId = $request->query('last_id');
        $repliesQuery = $ticket->replies();

        if ($lastId) {
            $repliesQuery->where('id', '>', $lastId);
        }

        $replies = $repliesQuery->get();
        $unreadCount = $ticket->replies->whereNotNull('admin_id')->where('is_read', 0)->count();

        if (!$ticket->has_user_read) {
            $ticket->update(['has_user_read' => true]);
        }
        $ticket->replies()->whereNotNull('admin_id')->where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'user_name' => $ticket->user->name ?? 'Agent',
            ],
            'unread_count' => $unreadCount,
            'replies' => $replies->map(function ($reply) use ($lastId) {
                static $dividerInserted = false;
                $isFirstUnread = false;

                // Only show divider on initial load (when last_id is null)
                if (!$lastId && !$dividerInserted && $reply->isFromAdmin() && !$reply->is_read) {
                    $isFirstUnread = true;
                    $dividerInserted = true;
                }

                return [
                    'id' => $reply->id,
                    'body' => $reply->body,
                    'image' => $reply->image ? asset('storage/' . $reply->image) : null,
                    'is_admin' => $reply->isFromAdmin(),
                    'sender' => $reply->isFromAdmin() ? ($reply->admin->name ?? 'Support') : 'You',
                    'time' => $reply->created_at->format('g:i A'),
                    'is_first_unread' => $isFirstUnread,
                ];
            })
        ]);
    }


}
