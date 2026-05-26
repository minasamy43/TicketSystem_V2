<?php

namespace App\Events;

use App\Models\Ticket;
use App\Services\RealtimePayloadService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $action = 'updated'
    ) {
        $this->ticket->loadMissing(['user', 'closer', 'inprogressBy']);
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admin')];

        $this->ticket->loadMissing('user');
        if ($this->ticket->user && (int) $this->ticket->user->role === 0) {
            $channels[] = new PrivateChannel('agent.' . $this->ticket->user_id);
        }
        if ($this->ticket->user && (int) $this->ticket->user->role === 2) {
            $channels[] = new PrivateChannel('user.' . $this->ticket->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'ticket.changed';
    }

    public function broadcastWith(): array
    {
        $payload = app(RealtimePayloadService::class);

        return [
            'action' => $this->action,
            'ticket' => $payload->formatTicketForAdminList($this->ticket),
            'agent_ticket' => $payload->formatTicketForAgentList($this->ticket),
            'user_ticket' => $payload->formatTicketForUserList($this->ticket),
            'update' => $payload->formatTicketUpdate($this->ticket, 'admin'),
            'agent_update' => $payload->formatTicketUpdate($this->ticket, 'agent'),
            'user_update' => $payload->formatTicketUpdate($this->ticket, 'user'),
            'dashboard' => $payload->getAdminDashboardStats(),
        ];
    }
}
