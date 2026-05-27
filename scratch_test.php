<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ticketId = 8; // An agent ticket
$ticket = App\Models\Ticket::findOrFail($ticketId);

// Let's simulate agent Mark replying to Admin
$agent = App\Models\User::where('role', 0)->first();

$reply = App\Models\Reply::create([
    'ticket_id' => $ticket->id,
    'user_id' => $agent->id,
    'admin_id' => null,
    'body' => 'Hello Admin, this is a test message from Agent ' . $agent->name . ' at ' . date('H:i:s'),
]);

event(new App\Events\ReplyCreated($reply));

echo "Broadcasted Agent reply for Ticket {$ticket->id}\n";
