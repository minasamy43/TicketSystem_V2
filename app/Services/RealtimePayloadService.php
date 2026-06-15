<?php

namespace App\Services;

use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RealtimePayloadService
{
    public function formatTicketForAdminList(Ticket $ticket): array
    {
        $ticket->loadMissing(['user', 'closer', 'inprogressBy']);
        $ticket->loadCount([
            'replies as unread_replies_count' => function ($query) {
                $query->where('is_read', 0)->whereNull('admin_id');
            },
        ]);

        return [
            'id' => $ticket->id,
            'user_name' => $ticket->user->name ?? 'N/A',
            'user_role' => $ticket->user->role ?? 2,
            'subject' => $ticket->subject,
            'status' => $ticket->status,
            'status_label' => ucfirst($ticket->status) . ($ticket->status == 'open' ? ' 🎟️' : ($ticket->status == 'closed' ? ' ✅️' : ' 👍🏻')),
            'inprogress_by' => $ticket->inprogressBy->name ?? '---',
            'closer' => $ticket->closer->name ?? '---',
            'time' => $ticket->created_at->format('g:i A'),
            'relative_time' => $ticket->created_at->diffForHumans(),
            'unread_count' => $ticket->unread_replies_count ?? 0,
            'created_date' => $ticket->created_at->format('Y-m-d'),
        ];
    }

    public function formatTicketForAgentList(Ticket $ticket): array
    {
        $ticket->loadMissing(['closer']);
        $ticket->loadCount([
            'replies as unread_replies_count' => function ($query) {
                $query->whereNotNull('admin_id')->where('is_read', 0);
            },
        ]);

        $status = $ticket->status;
        // Agent UI expects: open=red, in progress=yellow, closed=green
        $statusBg = match ($status) {
            'open' => 'rgba(220, 53, 69, 0.1)',
            'closed' => 'rgba(25, 135, 84, 0.1)',
            default => 'rgba(212, 175, 83, 0.15)', // in progress
        };
        $statusColor = match ($status) {
            'open' => '#dc3545',
            'closed' => '#198754',
            default => '#d4af53',
        };

        return [
            'id' => $ticket->id,
            'subject' => $ticket->subject,
            'status' => $status,
            'status_label' => ucfirst($status),
            'status_icon' => $status === 'open' ? '🎟️' : ($status === 'closed' ? '✅️' : '👍🏻'),
            'status_bg' => $statusBg,
            'status_color' => $statusColor,
            'closer' => $ticket->closer->name ?? '---',
            'time' => $ticket->created_at->format('g:i A'),
            'relative_time' => $ticket->created_at->diffForHumans(),
            'unread_count' => $ticket->unread_replies_count ?? 0,
            'created_date' => $ticket->created_at->format('Y-m-d'),
        ];
    }

    public function formatTicketForUserList(Ticket $ticket): array
    {
        $ticket->loadMissing(['closer']);
        $ticket->loadCount([
            'replies as unread_replies_count' => function ($query) {
                $query->whereNotNull('admin_id')->where('is_read', 0);
            },
        ]);

        $status = $ticket->status;
        $statusIcon = $status === 'open' ? '🎟️' : ($status === 'closed' ? '✅️' : '👍🏻');

        return [
            'id' => $ticket->id,
            'subject' => $ticket->subject,
            'status' => $status,
            'status_label' => ucfirst($status),
            'status_icon' => $statusIcon,
            'closer' => $ticket->closer->name ?? '---',
            'time' => $ticket->created_at->format('g:i A'),
            'relative_time' => $ticket->created_at->diffForHumans(),
            'unread_count' => $ticket->unread_replies_count ?? 0,
            'created_date' => $ticket->created_at->format('Y-m-d'),
        ];
    }

    public function formatTicketUpdate(Ticket $ticket, string $audience = 'admin'): array
    {
        $ticket->loadMissing(['closer', 'inprogressBy']);

        if ($audience === 'user') {
            $ticket->loadCount([
                'replies as unread_replies_count' => function ($query) {
                    $query->whereNotNull('admin_id')->where('is_read', 0);
                },
            ]);

            $status = $ticket->status;
            $statusIcon = $status === 'open' ? '🎟️' : ($status === 'closed' ? '✅️' : '👍🏻');

            return [
                'id' => $ticket->id,
                'status' => $status,
                'status_label' => ucfirst($status),
                'status_icon' => $statusIcon,
                'closer' => $ticket->closer->name ?? '---',
                'unread_count' => $ticket->unread_replies_count ?? 0,
            ];
        }

        if ($audience === 'agent') {
            $ticket->loadCount([
                'replies as unread_replies_count' => function ($query) {
                    $query->whereNotNull('admin_id')->where('is_read', 0);
                },
            ]);

            $status = $ticket->status;
            // Agent UI expects: open=red, in progress=yellow, closed=green
            $statusBg = match ($status) {
                'open' => 'rgba(220, 53, 69, 0.1)',
                'closed' => 'rgba(25, 135, 84, 0.1)',
                default => 'rgba(212, 175, 83, 0.15)', // in progress
            };
            $statusColor = match ($status) {
                'open' => '#dc3545',
                'closed' => '#198754',
                default => '#d4af53',
            };

            return [
                'id' => $ticket->id,
                'status' => $status,
                'status_label' => ucfirst($status),
                'status_icon' => $status === 'open' ? '🎟️' : ($status === 'closed' ? '✅️' : '👍🏻'),
                'status_bg' => $statusBg,
                'status_color' => $statusColor,
                'closer' => $ticket->closer->name ?? '---',
                'unread_count' => $ticket->unread_replies_count ?? 0,
            ];
        }

        $ticket->loadCount([
            'replies as unread_replies_count' => function ($query) {
                $query->where('is_read', 0)->whereNull('admin_id');
            },
        ]);

        return [
            'id' => $ticket->id,
            'status' => $ticket->status,
            'inprogress_by' => $ticket->inprogressBy->name ?? '---',
            'closer' => $ticket->closer->name ?? '---',
            'unread_count' => $ticket->unread_replies_count ?? 0,
        ];
    }

    public function formatReplyForChat(Reply $reply): array
    {
        $reply->loadMissing(['admin', 'user']);

        return [
            'id'             => $reply->id,
            'ticket_id'      => $reply->ticket_id,
            'body'           => $reply->body,
            'image'          => $reply->image ? asset('storage/' . $reply->image) : null,
            'video'          => $reply->video ? asset('storage/' . $reply->video) : null,
            'is_admin'       => $reply->isFromAdmin(),
            'sender'         => $reply->isFromAdmin()
                ? ($reply->admin->name ?? 'Admin')
                : ($reply->user->name ?? 'User'),
            'time'           => $reply->created_at->format('g:i A'),
            'is_first_unread' => false,
        ];
    }

    public function formatMessageForInbox(Reply $reply): array
    {
        $reply->loadMissing(['ticket.user']);

        return [
            'id' => $reply->id,
            'ticket_id' => $reply->ticket_id,
            'user_name' => $reply->ticket->user->name ?? 'Unknown User',
            'body' => mb_strimwidth($reply->body, 0, 100, '...'),
            'image' => $reply->image,
            'is_read' => $reply->is_read,
            'is_from_admin' => $reply->isFromAdmin(),
            'relative_time' => $reply->created_at->diffForHumans(),
        ];
    }

    public function getAdminDashboardStats(?int $month = null, ?int $year = null): array
    {
        $today = now()->format('Y-m-d');
        $month = $month ?? (int) now()->month;
        $year = $year ?? (int) now()->year;
        $monthStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return [
            'counts' => [
                'total' => Ticket::whereDate('created_at', $today)->count(),
                'open' => Ticket::whereDate('created_at', $today)->where('status', 'open')->count(),
                'closed' => Ticket::whereDate('created_at', $today)->where('status', 'closed')->count(),
                'in_progress' => Ticket::whereDate('created_at', $today)->where('status', 'in progress')->count(),
            ],
            'monthly_counts' => [
                'open' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'open')->count(),
                'in_progress' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'in progress')->count(),
                'closed' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'closed')->count(),
                'agent_count' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->whereHas('user', fn ($q) => $q->where('role', 0))->count(),
                'user_count' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->whereHas('user', fn ($q) => $q->where('role', 2))->count(),
            ],
            'today_label' => now()->format('M d'),
        ];
    }

    public function getAdminSidebarCounts(?User $admin = null): array
    {
        $admin = $admin ?? Auth::user();
        if (!$admin) {
            return [];
        }

        $agentUnread = Ticket::whereHas('user', fn ($q) => $q->where('role', 0))
            ->where('has_admin_read', false)
            ->count();

        $userUnread = Ticket::whereHas('user', fn ($q) => $q->where('role', 2))
            ->where('has_admin_read', false)
            ->count();

        $totalUnread = Reply::whereNull('admin_id')
            ->where('is_read', 0)
            ->whereIn('ticket_id', function ($query) use ($admin) {
                $query->select('id')->from('tickets')
                    ->where('inprogress_by', $admin->id)
                    ->orWhere('closed_by', $admin->id)
                    ->orWhereNull('inprogress_by')
                    ->orWhereIn('id', function ($sub) use ($admin) {
                        $sub->select('ticket_id')->from('replies')->where('admin_id', $admin->id);
                    });
            })->count();

        return [
            'agent_tickets' => $agentUnread,
            'user_tickets' => $userUnread,
            'total_tickets' => $agentUnread + $userUnread,
            'messages' => $totalUnread,
        ];
    }

    public function getAgentSidebarCounts(int $userId): array
    {
        $unread = Reply::whereNotNull('admin_id')
            ->where('is_read', 0)
            ->whereIn('ticket_id', function ($query) use ($userId) {
                $query->select('id')->from('tickets')->where('user_id', $userId);
            })->count();

        return ['messages' => $unread];
    }

    public function getAdminUnreadCountsMap(): array
    {
        return Ticket::withCount([
            'replies as unread_count' => function ($q) {
                $q->whereNull('admin_id')->where('is_read', 0);
            },
        ])->having('unread_count', '>', 0)
            ->pluck('unread_count', 'id')
            ->toArray();
    }

    public function getAgentUnreadCountsMap(int $userId): array
    {
        return Ticket::where('user_id', $userId)
            ->withCount([
                'replies as unread_count' => function ($q) {
                    $q->whereNotNull('admin_id')->where('is_read', 0);
                },
            ])->having('unread_count', '>', 0)
            ->pluck('unread_count', 'id')
            ->toArray();
    }

    public function getUserUnreadCountsMap(int $userId): array
    {
        return $this->getAgentUnreadCountsMap($userId);
    }
}
