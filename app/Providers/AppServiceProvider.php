<?php

namespace App\Providers;

use App\Models\Reply;
use App\Models\Ticket;
use App\Observers\ReplyObserver;
use App\Observers\TicketObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        Ticket::observe(TicketObserver::class);
        Reply::observe(ReplyObserver::class);
    }

}
