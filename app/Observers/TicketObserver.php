<?php

namespace App\Observers;

use App\Events\TicketChanged;
use App\Events\TicketDeleted;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        $this->safeBroadcast(new TicketChanged($ticket, 'created'));
    }

    public function updated(Ticket $ticket): void
    {
        if (!$ticket->wasChanged(['status', 'inprogress_by', 'closed_by', 'has_admin_read', 'has_user_read'])) {
            return;
        }

        $this->safeBroadcast(new TicketChanged($ticket, 'updated'));
    }

    public function deleted(Ticket $ticket): void
    {
        $this->safeBroadcast(TicketDeleted::fromTicket($ticket));
    }

    private function safeBroadcast(object $event): void
    {
        try {
            broadcast($event);
        } catch (\Throwable $e) {
            Log::warning('Realtime broadcast failed (is Reverb running? php artisan reverb:start): ' . $e->getMessage());
        }
    }
}
