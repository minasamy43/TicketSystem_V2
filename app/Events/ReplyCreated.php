<?php

namespace App\Events;

use App\Models\Reply;
use App\Services\RealtimePayloadService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplyCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Reply $reply)
    {
        $this->reply->loadMissing(['ticket.user', 'admin', 'user']);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin'),
            new PrivateChannel('ticket.' . $this->reply->ticket_id),
        ];

        $ticket = $this->reply->ticket;
        if ($ticket && $ticket->user && (int) $ticket->user->role === 0) {
            $channels[] = new PrivateChannel('agent.' . $ticket->user_id);
        }
        if ($ticket && $ticket->user && (int) $ticket->user->role === 2) {
            $channels[] = new PrivateChannel('user.' . $ticket->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'reply.created';
    }

    public function broadcastWith(): array
    {
        $payload = app(RealtimePayloadService::class);
        $ticket = $this->reply->ticket;

        $agentCounts = ($ticket && $ticket->user && (int) $ticket->user->role === 0)
            ? $payload->getAgentUnreadCountsMap($ticket->user_id)
            : [];

        $userCounts = ($ticket && $ticket->user && (int) $ticket->user->role === 2)
            ? $payload->getUserUnreadCountsMap($ticket->user_id)
            : [];

        return [
            'reply' => $payload->formatReplyForChat($this->reply),
            'message' => $payload->formatMessageForInbox($this->reply),
            'ticket_id' => $this->reply->ticket_id,
            'ticket_update' => $ticket ? $payload->formatTicketUpdate($ticket, 'admin') : null,
            'agent_ticket_update' => $ticket ? $payload->formatTicketUpdate($ticket, 'agent') : null,
            'user_ticket_update' => $ticket ? $payload->formatTicketUpdate($ticket, 'user') : null,
            'unread_counts' => [
                'admin' => $payload->getAdminUnreadCountsMap(),
                'agent' => $agentCounts,
                'user' => $userCounts,
            ],
        ];
    }
}
