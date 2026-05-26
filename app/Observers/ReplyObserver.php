<?php

namespace App\Observers;

use App\Events\ReplyCreated;
use App\Models\Reply;
use Illuminate\Support\Facades\Log;

class ReplyObserver
{
    public function created(Reply $reply): void
    {
        $reply->loadMissing('ticket');
        try {
            broadcast(new ReplyCreated($reply));
        } catch (\Throwable $e) {
            Log::warning('Realtime broadcast failed (is Reverb running? php artisan reverb:start): ' . $e->getMessage());
        }
    }
}
