<?php

namespace App\Events;

use App\Models\Ticket;
use App\Services\RealtimePayloadService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $ticketId,
        public int $userId,
        public int $userRole
    ) {}

    public static function fromTicket(Ticket $ticket): self
    {
        $ticket->loadMissing('user');

        return new self(
            (int) $ticket->id,
            (int) $ticket->user_id,
            (int) ($ticket->user->role ?? 2)
        );
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admin')];

        if ($this->userRole === 0) {
            $channels[] = new PrivateChannel('agent.' . $this->userId);
        }
        if ($this->userRole === 2) {
            $channels[] = new PrivateChannel('user.' . $this->userId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'ticket.deleted';
    }

    public function broadcastWith(): array
    {
        $payload = app(RealtimePayloadService::class);

        return [
            'id' => $this->ticketId,
            'dashboard' => $payload->getAdminDashboardStats(),
        ];
    }
}
