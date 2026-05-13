<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = Auth::id();
        $date = $request->query('date');

        $ticketsQuery = Ticket::where('user_id', $userId)
                        ->withCount(['replies as unread_replies_count' => function($query) {
                            $query->whereNotNull('admin_id')->where('is_read', 0);
                        }]);
        
        if ($request->filled('subject')) {
            $ticketsQuery->where('subject', 'like', '%' . $request->subject . '%');
        }
        if ($request->filled('status')) {
            $ticketsQuery->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $ticketsQuery->whereDate('created_at', $request->date);
        }

        $tickets = $ticketsQuery->latest()->paginate(10);

        // Stats for the cards
        $statsQuery = Ticket::where('user_id', $userId);
        if ($date) {
            $statsQuery->whereDate('created_at', $date);
        }
        
        $openTickets = (clone $statsQuery)->where('status', 'open')->count();
        $inProgress = (clone $statsQuery)->where('status', 'in progress')->count();
        $closedTickets = (clone $statsQuery)->where('status', 'closed')->count();
        $totalTickets = $statsQuery->count();

        return view('user.dashboard', compact('tickets', 'openTickets', 'inProgress', 'closedTickets', 'totalTickets', 'date'));
    }

    public function getNewTicketsData(Request $request)
    {
        $userId = Auth::id();
        $lastId = $request->get('last_id', 0);
        $date = $request->get('date');
        $existingIds = $request->get('existing_ids', []);

        $query = Ticket::with(['closer'])
            ->where('user_id', $userId)
            ->where('id', '>', $lastId)
            ->withCount(['replies as unread_replies_count' => function($query) {
                $query->whereNotNull('admin_id')->where('is_read', 0);
            }]);

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        if ($subject = $request->get('subject')) {
            $query->where('subject', 'like', '%' . $subject . '%');
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $newTickets = $query->latest()->get();

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

    public function createTicket()
    {
        return view('user.tickets.create');
    }

    public function storeTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|max:64',
            'message' => 'required|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
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

        return redirect()->route('user.dashboard')->with('success', 'Ticket created successfully');
    }

    public function showTicket($id)
    {
        $ticket = Ticket::with([
            'user',
            'replies' => function ($q) {
                $q->with('admin', 'user')->oldest();
            }
        ])->findOrFail($id);

        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('user.tickets.show', compact('ticket'));
    }

    public function replyTicket(Request $request, $id)
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

        \App\Models\Reply::create([
            'ticket_id' => $id,
            'user_id' => Auth::id(),
            'admin_id' => null,
            'body' => $request->body ?? '',
            'image' => $imagePath,
        ]);

        // Mark as unread for the admin
        Ticket::where('id', $id)->update(['has_admin_read' => false]);

        return back()->with('success', 'Reply sent.');
    }
}
