<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TicketController extends Controller
{
    /** List all tickets with filters. */
    public function index()
    {
        $defaultDate = now()->format('Y-m-d');
        $date = request()->filled('date') ? request('date') : $defaultDate;

        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $query = Ticket::with(['user', 'closer', 'inprogressBy'])
            ->withCount([
                'replies as unread_replies_count' => function ($query) {
                    $query->where('is_read', 0)->whereNull('admin_id');
                }
            ]);

        if ($dateFrom && $dateTo) {
            $query->whereDate('created_at', '>=', $dateFrom)
                  ->whereDate('created_at', '<=', $dateTo);
        } else {
            $query->whereDate('created_at', $date);
        }

        if ($ticketId = request('ticket_id')) {
            $query->where('id', $ticketId);
        }

        if ($priority = request('priority')) {
            $query->where('priority', $priority);
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($category = request('category')) {
            $query->where('category', $category);
        }

        if ($userName = request('user_name')) {
            $query->whereHas('user', function ($q) use ($userName) {
                $q->where('name', 'like', '%' . $userName . '%');
            });
        }

        if ($subject = request('subject')) {
            $query->where('subject', 'like', '%' . $subject . '%');
        }

        if ($closerName = request('closer_name')) {
            $query->whereHas('closer', function ($q) use ($closerName) {
                $q->where('name', 'like', '%' . $closerName . '%');
            });
        }

        if ($inprogressName = request('inprogress_name')) {
            $query->whereHas('inprogressBy', function ($q) use ($inprogressName) {
                $q->where('name', 'like', '%' . $inprogressName . '%');
            });
        }

        // Filter by sender type: 'agent' = role 0, 'user' = role 2
        if ($senderType = request('sender_type')) {
            $role = $senderType === 'agent' ? 0 : 2;
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        return view('admin.tickets.index', compact('tickets', 'date'));
    }
    /** Show ticket details with comments. */
    public function show($id)
    {
        $ticket = Ticket::with(['user', 'replies.admin'])->withCount([
            'replies as unread_replies_count' => function ($query) {
                $query->whereNull('admin_id')->where('is_read', 0);
            }
        ])->findOrFail($id);

        // Mark as read when the full page is opened
        if (!$ticket->has_admin_read) {
            $ticket->update(['has_admin_read' => true]);
        }
        
        // Clear cached sidebar unread count
        $adminId = \Illuminate\Support\Facades\Auth::id();
        \Illuminate\Support\Facades\Cache::forget('admin_sidebar_unread_' . $adminId);
        \Illuminate\Support\Facades\Cache::forget('admin_sidebar_agent_tickets_unread_' . $adminId);
        \Illuminate\Support\Facades\Cache::forget('admin_sidebar_user_tickets_unread_' . $adminId);

        return view('admin.tickets.show-ticket', compact('ticket'));
    }

    /** Update the ticket status. */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in progress,closed',
        ]);

        $ticket = Ticket::findOrFail($id);
        $currentUserId = Auth::id();

        // Exclusive Lock Logic: If target status is 'in progress' or 'closed'
        if (in_array($request->status, ['in progress', 'closed'])) {
            $ownerId = $ticket->inprogress_by ?: $ticket->closed_by;
            if ($ownerId && $ownerId !== $currentUserId) {
                $ownerName = $ticket->inprogressBy->name ?? ($ticket->closer->name ?? 'another admin');
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized: This ticket is already being handled by {$ownerName}."
                ], 403);
            }
        }

        // Reopen restriction: only the progress member or the closer can set status back to 'open'
        if ($request->status === 'open') {
            $ownerId = $ticket->inprogress_by ?: $ticket->closed_by;
            if ($ownerId && $ownerId !== $currentUserId) {
                $ownerName = $ticket->inprogressBy->name ?? ($ticket->closer->name ?? 'another admin');
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized: Only {$ownerName} (who handled this ticket) can reopen it."
                ], 403);
            }
        }

        $updateData = ['status' => $request->status];

        if ($request->status === 'closed') {
            $updateData['closed_by'] = $currentUserId;
            $updateData['solved_at'] = now();
        } elseif ($request->status === 'in progress') {
            $updateData['inprogress_by'] = $currentUserId;
            $updateData['closed_by'] = null; // Clear closer if moved back to progress
            $updateData['solved_at'] = null;
        } else {
            // Re-opening: Clear both
            $updateData['closed_by'] = null;
            $updateData['inprogress_by'] = null;
            $updateData['solved_at'] = null;
        }

        $ticket->update($updateData);
        $ticket->load(['inprogressBy', 'closer']);

        if ($request->ajax()) {
            // Calculate resolution time for display
            $resolutionTime = null;
            if ($ticket->solved_at) {
                $diffMins  = (int) $ticket->created_at->diffInMinutes($ticket->solved_at);
                $diffHours = (int) $ticket->created_at->diffInHours($ticket->solved_at);
                $diffDays  = (int) $ticket->created_at->diffInDays($ticket->solved_at);

                if ($diffMins < 60) {
                    $resolutionTime = $diffMins . ' min' . ($diffMins !== 1 ? 's' : '');
                } elseif ($diffHours < 24) {
                    $remainMins = $diffMins - ($diffHours * 60);
                    $resolutionTime = $diffHours . ' hr' . ($diffHours !== 1 ? 's' : '');
                    if ($remainMins > 0) $resolutionTime .= ' ' . $remainMins . ' min' . ($remainMins !== 1 ? 's' : '');
                } else {
                    $remainHours = $diffHours - ($diffDays * 24);
                    $resolutionTime = $diffDays . ' day' . ($diffDays !== 1 ? 's' : '');
                    if ($remainHours > 0) $resolutionTime .= ' ' . $remainHours . ' hr' . ($remainHours !== 1 ? 's' : '');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'new_status' => $ticket->status,
                'closer' => $ticket->closer ? $ticket->closer->name : '---',
                'inprogress_by' => $ticket->inprogressBy ? $ticket->inprogressBy->name : '---',
                'solved_at' => $ticket->solved_at ? $ticket->solved_at->format('M d, Y \a\t g:i A') : null,
                'resolution_time' => $resolutionTime,
            ]);
        }

        return back()->with('success', 'Status updated successfully.');
    }

    /** Get ticket chat data for AJAX popup. */
    public function getChatData(Request $request, $id)
    {
        $ticket = Ticket::with(['user', 'replies.admin'])->findOrFail($id);

        $lastId = $request->query('last_id');
        $repliesQuery = $ticket->replies();

        if ($lastId) {
            $repliesQuery->where('id', '>', $lastId);
        }

        $replies = $repliesQuery->get();

        $unreadCount = 0;
        if (!$lastId) {
            $unreadCount = $ticket->replies()->whereNull('admin_id')->where('is_read', 0)->count();
            $this->markConversationAsRead($ticket);
        }

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'user_name' => $ticket->user->name ?? 'User',
            ],
            'unread_count' => $unreadCount,
            'replies' => $replies->map(function ($reply) use ($lastId) {
                static $dividerInserted = false;
                $isFirstUnread = false;

                // Only show divider on initial load
                if (!$lastId && !$dividerInserted && !$reply->isFromAdmin() && !$reply->is_read) {
                    $isFirstUnread = true;
                    $dividerInserted = true;
                }

                return [
                    'id'             => $reply->id,
                    'body'           => $reply->body,
                    'image'          => $reply->image ? asset('storage/' . $reply->image) : null,
                    'video'          => $reply->video ? asset('storage/' . $reply->video) : null,
                    'is_admin'       => $reply->isFromAdmin(),
                    'sender'         => $reply->isFromAdmin() ? ($reply->admin->name ?? 'Admin') : ($reply->user->name ?? 'User'),
                    'time'           => $reply->created_at->format('g:i A'),
                    'is_first_unread' => $isFirstUnread,
                ];
            })
        ]);
    }

    /** Mark ticket conversation as read (admin or agent). */
    public function markAsRead($id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->markConversationAsRead($ticket);

        return response()->json([
            'success' => true,
            'counts' => $this->unreadCountsForCurrentUser(),
        ]);
    }

    /** Store a new admin comment on the ticket. */
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'body'  => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo|max:51200',
        ]);

        if (!$request->body && !$request->hasFile('image') && !$request->hasFile('video')) {
            return response()->json(['success' => false, 'message' => 'Message, image, or video is required.'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('tickets', $filename, 'public');
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
            $videoPath = $video->storeAs('tickets/videos', $filename, 'public');
        }

        $reply = Reply::create([
            'ticket_id' => $id,
            'admin_id'  => Auth::id(),
            'body'      => $request->body ?? '',
            'image'     => $imagePath,
            'video'     => $videoPath,
        ]);

        // Mark as unread for the user
        Ticket::where('id', $id)->update(['has_user_read' => false]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment posted.',
                'reply'   => [
                    'id'       => $reply->id,
                    'body'     => $reply->body,
                    'is_admin' => true,
                    'image'    => $reply->image ? asset('storage/' . $reply->image) : null,
                    'video'    => $reply->video ? asset('storage/' . $reply->video) : null,
                    'sender'   => Auth::user()->name ?? 'Admin',
                    'time'     => $reply->created_at->format('g:i A'),
                ]
            ]);
        }

        return back()->with('success', 'Comment posted.');
    }

    /** Get new tickets data for real-time discovery. */
    public function getNewTicketsData(Request $request)
    {
        $lastId = $request->get('last_id', 0);
        $date = $request->get('date', now()->format('Y-m-d'));

        $query = Ticket::with(['user', 'closer', 'inprogressBy'])
            ->withCount([
                'replies as unread_replies_count' => function ($query) {
                    $query->where('is_read', 0)->whereNull('admin_id');
                }
            ])
            ->where('id', '>', $lastId)
            ->whereDate('created_at', $date);

        // Apply filters (same as DashboardController)
        if ($ticketId = $request->get('ticket_id'))
            $query->where('id', $ticketId);
        if ($priority = $request->get('priority'))
            $query->where('priority', $priority);
        if ($status = $request->get('status'))
            $query->where('status', $status);
        if ($category = $request->get('category'))
            $query->where('category', $category);
        if ($userName = $request->get('user_name')) {
            $query->whereHas('user', function ($q) use ($userName) {
                $q->where('name', 'like', '%' . $userName . '%'); });
        }
        if ($subject = $request->get('subject'))
            $query->where('subject', 'like', '%' . $subject . '%');
        if ($closerName = $request->get('closer_name')) {
            $query->whereHas('closer', function ($q) use ($closerName) {
                $q->where('name', 'like', '%' . $closerName . '%'); });
        }
        if ($inprogressName = $request->get('inprogress_name')) {
            $query->whereHas('inprogressBy', function ($q) use ($inprogressName) {
                $q->where('name', 'like', '%' . $inprogressName . '%'); });
        }
        if ($senderType = $request->get('sender_type')) {
            $role = $senderType === 'agent' ? 0 : 2;
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        $newTickets = $query->latest()->get();

        // Today's counts for the dashboard stat cards (must match DashboardController)
        $today = now()->format('Y-m-d');
        $counts = [
            'total' => Ticket::whereDate('created_at', $today)->count(),
            'open' => Ticket::whereDate('created_at', $today)->where('status', 'open')->count(),
            'closed' => Ticket::whereDate('created_at', $today)->where('status', 'closed')->count(),
            'in_progress' => Ticket::whereDate('created_at', $today)->where('status', 'in progress')->count(),
        ];

        // Monthly totals for the distribution chart
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $monthly_counts = [
            'open' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'open')->count(),
            'in_progress' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'in progress')->count(),
            'closed' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'closed')->count(),
            'agent_count' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->whereHas('user', function($q) { $q->where('role', 0); })->count(),
            'user_count' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->whereHas('user', function($q) { $q->where('role', 2); })->count(),
        ];

        $updates = [];
        $existingIds = $request->get('existing_ids', []);
        if (!empty($existingIds)) {
            $updatedTickets = Ticket::with(['closer', 'inprogressBy'])
                ->whereIn('id', $existingIds)
                ->withCount([
                    'replies as unread_replies_count' => function ($query) {
                        $query->where('is_read', 0)->whereNull('admin_id');
                    }
                ])->get();

            foreach ($updatedTickets as $t) {
                $updates[] = [
                    'id' => $t->id,
                    'status' => $t->status,
                    'inprogress_by' => $t->inprogressBy->name ?? '---',
                    'closer' => $t->closer->name ?? '---',
                    'unread_count' => $t->unread_replies_count,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'new_tickets' => $newTickets->map(function ($t) {
                return [
                    'id' => $t->id,
                    'user_name' => $t->user->name ?? 'N/A',
                    'user_role' => $t->user->role ?? 2,
                    'subject' => $t->subject,
                    'category' => ucfirst($t->category ?? 'None'),
                    'status' => $t->status,
                    'status_label' => ucfirst($t->status) . ($t->status == 'open' ? ' 🎟️' : ($t->status == 'closed' ? ' ✅️' : ' 👍🏻')),
                    'inprogress_by' => $t->inprogressBy->name ?? '---',
                    'closer' => $t->closer->name ?? '---',
                    'time' => $t->created_at->format('g:i A'),
                    'relative_time' => $t->created_at->diffForHumans(),
                    'unread_count' => $t->unread_replies_count,
                    'can_reopen' => true, 
                ];
            }),
            'counts' => $counts,
            'monthly_counts' => $monthly_counts,
            'today_label' => now()->format('M d'),
            'new_highest_id' => $newTickets->max('id') ?: $lastId,
            'updates' => $updates
        ]);
    }

    /** Get unread counts for all relevant tickets. */
    public function getUnreadCounts()
    {
        return response()->json([
            'success' => true,
            'counts' => $this->unreadCountsForCurrentUser(),
        ]);
    }

    private function unreadCountsForCurrentUser(): array
    {
        $user = Auth::user();
        if ((int) $user->role === 1) {
            $tickets = Ticket::withCount([
                'replies as unread_count' => function ($q) {
                    $q->whereNull('admin_id')->where('is_read', 0);
                },
            ])->having('unread_count', '>', 0)->get();
        } else {
            $tickets = Ticket::where('user_id', $user->id)
                ->withCount([
                    'replies as unread_count' => function ($q) {
                        $q->whereNotNull('admin_id')->where('is_read', 0);
                    },
                ])->having('unread_count', '>', 0)->get();
        }

        return $tickets->pluck('unread_count', 'id')->toArray();
    }

    public function markConversationAsRead(Ticket $ticket): void
    {
        $user = Auth::user();

        if ((int) $user->role === 1) {
            if (!$ticket->has_admin_read) {
                $ticket->update(['has_admin_read' => true]);
            }
            $ticket->replies()->whereNull('admin_id')->where('is_read', false)->update(['is_read' => true]);

            $adminId = $user->id;
            Cache::forget('admin_sidebar_unread_' . $adminId);
            Cache::forget('admin_sidebar_agent_tickets_unread_' . $adminId);
            Cache::forget('admin_sidebar_user_tickets_unread_' . $adminId);

            return;
        }

        if ((int) $user->role === 0 && (int) $ticket->user_id === (int) $user->id) {
            if (!$ticket->has_user_read) {
                $ticket->update(['has_user_read' => true]);
            }
            $ticket->replies()->whereNotNull('admin_id')->where('is_read', false)->update(['is_read' => true]);
            Cache::forget('user_sidebar_unread_' . $user->id);

            return;
        }

        if ((int) $user->role === 2 && (int) $ticket->user_id === (int) $user->id) {
            if (!$ticket->has_user_read) {
                $ticket->update(['has_user_read' => true]);
            }
            $ticket->replies()->whereNotNull('admin_id')->where('is_read', false)->update(['is_read' => true]);
            Cache::forget('user_sidebar_unread_' . $user->id);

            return;
        }

        abort(403, 'Unauthorized');
    }

    /** Get report data for a date range (used by PDF/Excel export). */
    public function getReportData(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->format('Y-m-d'));
        $dateTo   = $request->get('date_to',   now()->format('Y-m-d'));

        $query = Ticket::with(['user', 'closer', 'inprogressBy'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        // Honour the same sender_type filter if present
        if ($senderType = $request->get('sender_type')) {
            $role = $senderType === 'agent' ? 0 : 2;
            $query->whereHas('user', fn($q) => $q->where('role', $role));
        }

        $tickets = $query->latest()->get();

        return response()->json([
            'success' => true,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'total'     => $tickets->count(),
            'tickets'   => $tickets->map(function($t) {
                $resolutionTime = '---';
                if ($t->solved_at) {
                    $diffMins  = (int) $t->created_at->diffInMinutes($t->solved_at);
                    $diffHours = (int) $t->created_at->diffInHours($t->solved_at);
                    $diffDays  = (int) $t->created_at->diffInDays($t->solved_at);
                    if ($diffMins < 60) {
                        $resolutionTime = $diffMins . ' min' . ($diffMins !== 1 ? 's' : '');
                    } elseif ($diffHours < 24) {
                        $remainMins = $diffMins - ($diffHours * 60);
                        $resolutionTime = $diffHours . ' hr' . ($diffHours !== 1 ? 's' : '');
                        if ($remainMins > 0) $resolutionTime .= ' ' . $remainMins . ' min' . ($remainMins !== 1 ? 's' : '');
                    } else {
                        $remainHours = $diffHours - ($diffDays * 24);
                        $resolutionTime = $diffDays . ' day' . ($diffDays !== 1 ? 's' : '');
                        if ($remainHours > 0) $resolutionTime .= ' ' . $remainHours . ' hr' . ($remainHours !== 1 ? 's' : '');
                    }
                }
                return [
                    'id'              => $t->id,
                    'sender'          => $t->user->name ?? 'N/A',
                    'category'        => ucfirst($t->category ?? 'None'),
                    'subject'         => $t->subject,
                    'status'          => ucfirst($t->status),
                    'inprogress_by'   => $t->inprogressBy->name ?? '---',
                    'closed_by'       => $t->closer->name ?? '---',
                    'solved_at'       => $t->solved_at ? $t->solved_at->format('Y-m-d g:i A') : '---',
                    'created_at'      => $t->created_at->format('Y-m-d g:i A'),
                    'resolution_time' => $resolutionTime,
                ];
            }),
        ]);
    }

    /** Get dates that have unread tickets. */
    public function getUnreadDates(Request $request)
    {
        $query = Ticket::where('has_admin_read', false);

        if ($senderType = $request->get('sender_type')) {
            $role = $senderType === 'agent' ? 0 : 2;
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        $dates = $query->selectRaw('DATE(created_at) as date')
            ->groupBy('date')
            ->pluck('date');

        return response()->json($dates);
    }
    /** Get aggregate unread counts for the sidebar (Admin and Agent/User). */
    public function getSidebarAggregateCounts()
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false]);

        $data = [
            'success' => true,
            'role' => $user->role,
        ];

        if ($user->role == 1) {
            // Admin counts
            $agentUnread = Ticket::whereHas('user', function($q) { $q->where('role', 0); })
                ->where('has_admin_read', false)
                ->count();
            
            $userUnread = Ticket::whereHas('user', function($q) { $q->where('role', 2); })
                ->where('has_admin_read', false)
                ->count();

            $totalUnread = Reply::whereNull('admin_id')
                ->where('is_read', 0)
                ->whereIn('ticket_id', function ($query) use ($user) {
                    $query->select('id')->from('tickets')
                        ->where('inprogress_by', $user->id)
                        ->orWhere('closed_by', $user->id)
                        ->orWhereNull('inprogress_by')
                        ->orWhereIn('id', function ($sub) use ($user) {
                            $sub->select('ticket_id')->from('replies')->where('admin_id', $user->id);
                        });
                })->count();

            $data['admin'] = [
                'agent_tickets' => $agentUnread,
                'user_tickets' => $userUnread,
                'total_tickets' => $agentUnread + $userUnread,
                'messages' => $totalUnread
            ];
        } else {
            // Agent (User role 0) or User (User role 2) counts
            $unread = Reply::whereNotNull('admin_id')
                ->where('is_read', 0)
                ->whereIn('ticket_id', function ($query) use ($user) {
                    $query->select('id')->from('tickets')->where('user_id', $user->id);
                })->count();

            $data['user'] = [
                'messages' => $unread
            ];
        }

        return response()->json($data);
    }
}
