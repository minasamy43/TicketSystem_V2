<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin', function ($user) {
    return (int) $user->role === 1;
});

Broadcast::channel('agent.{id}', function ($user, $id) {
    return (int) $user->role === 0 && (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->role === 2 && (int) $user->id === (int) $id;
});

Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }

    if ((int) $user->role === 1) {
        return true;
    }

    if ((int) $user->role === 0 && (int) $ticket->user_id === (int) $user->id) {
        return true;
    }

    if ((int) $user->role === 2 && (int) $ticket->user_id === (int) $user->id) {
        return true;
    }

    return false;
});
