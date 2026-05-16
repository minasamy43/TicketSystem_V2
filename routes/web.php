<?php

use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\TicketController;
use Illuminate\Support\Facades\Route;

/*
| Web Routes
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

//General routes
Route::get('login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('register', [App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register');
Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('tickets/unread-counts', [App\Http\Controllers\Admin\TicketController::class, 'getUnreadCounts'])->name('tickets.unread-counts')->middleware('auth');
Route::get('admin/tickets/new-data', [App\Http\Controllers\Admin\TicketController::class, 'getNewTicketsData'])->name('admin.tickets.new-data')->middleware(['auth', 'admin']); //for ajax for realtime data update
Route::get('admin/tickets/unread-dates', [App\Http\Controllers\Admin\TicketController::class, 'getUnreadDates'])->name('admin.tickets.unread-dates')->middleware(['auth', 'admin']);
// Knowledge Base routes accessible to both User and Admin (for Preview only)
Route::middleware('auth')->group(function () {
    Route::get('agent/knowledge-base', [App\Http\Controllers\Agent\KnowledgeBaseController::class, 'index'])->name('knowledge.base');
    Route::get('agent/knowledge-base/category/{slug}', [App\Http\Controllers\Agent\KnowledgeBaseController::class, 'showCategory'])->name('knowledge.category');
    Route::get('agent/knowledge-base/article/{slug}', [App\Http\Controllers\Agent\KnowledgeBaseController::class, 'showArticle'])->name('knowledge.article');
});



// (Agent dashboard)  
Route::middleware(['auth', 'agent'])->group(function () {
    Route::get('agent/dashboard', [DashboardController::class, 'index'])->name('agent.dashboard');
    Route::get('agent/dashboard/new-data', [DashboardController::class, 'getNewTicketsData'])->name('agent.dashboard.new-data'); //for ajax for realtime data update
    Route::get('agent/tickets/create', [TicketController::class, 'create'])->name('agent.tickets.create');
    Route::post('agent/tickets/store', [TicketController::class, 'store'])->name('agent.tickets.store');
    Route::get('agent/tickets/{id}', [TicketController::class, 'show'])->name('agent.tickets.show');
    Route::get('agent/tickets/{id}/chat-data', [TicketController::class, 'getChatData'])->name('agent.tickets.chat-data');
    Route::post('agent/tickets/{id}/reply', [TicketController::class, 'reply'])->name('agent.tickets.reply');
    Route::post('agent/tickets/{id}/close', [TicketController::class, 'close'])->name('agent.tickets.close');
    Route::delete('agent/tickets/{id}', [TicketController::class, 'destroy'])->name('agent.tickets.destroy');
    // Agent Messages
    Route::get('agent/messages', [\App\Http\Controllers\Agent\MessageController::class, 'index'])->name('agent.messages.index');
    Route::get('agent/messages/new-data', [\App\Http\Controllers\Agent\MessageController::class, 'getNewMessagesData'])->name('agent.messages.new-data');
    Route::get('agent/messages/unread-dates', [\App\Http\Controllers\Agent\MessageController::class, 'getUnreadMessageDates'])->name('agent.messages.unread-dates');
    // Agent Settings
    Route::get('agent/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('agent.settings');
    Route::post('agent/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('agent.settings.profile');
    Route::post('agent/settings/password', [\App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('agent.settings.password');
});

// (User dashboard)
Route::middleware(['auth', 'user'])->group(function () {
    Route::get('user/dashboard', [App\Http\Controllers\User\UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('user/dashboard/new-data', [App\Http\Controllers\User\UserController::class, 'getNewTicketsData'])->name('user.dashboard.new-data');
    Route::get('user/tickets/create', [App\Http\Controllers\User\UserController::class, 'createTicket'])->name('user.tickets.create');
    Route::post('user/tickets/store', [App\Http\Controllers\User\UserController::class, 'storeTicket'])->name('user.tickets.store');
    Route::get('user/tickets/{id}', [App\Http\Controllers\User\UserController::class, 'showTicket'])->name('user.tickets.show');
    Route::post('user/tickets/{id}/reply', [App\Http\Controllers\User\UserController::class, 'replyTicket'])->name('user.tickets.reply');
    Route::get('user/tickets/{id}/chat-data', [App\Http\Controllers\User\UserController::class, 'getChatData'])->name('user.tickets.chat-data');
    Route::delete('user/tickets/{id}', [App\Http\Controllers\User\UserController::class, 'destroyTicket'])->name('user.tickets.destroy');

    // User Settings
    Route::get('user/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('user.settings');
    Route::post('user/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('user.settings.profile');
    Route::post('user/settings/password', [\App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('user.settings.password');
});


// (Admin dashboard)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('admin/tickets', [App\Http\Controllers\Admin\TicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('admin/tickets/{id}', [App\Http\Controllers\Admin\TicketController::class, 'show'])->name('admin.tickets.show');
    Route::post('admin/tickets/{id}/status', [App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('admin.tickets.status');
    Route::post('admin/tickets/{id}/comment', [App\Http\Controllers\Admin\TicketController::class, 'storeComment'])->name('admin.tickets.comment');
    Route::get('admin/tickets/{id}/chat-data', [App\Http\Controllers\Admin\TicketController::class, 'getChatData'])->name('admin.tickets.chat-data');
    // admin user management
    Route::get('admin/agents', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.agents.index');
    Route::get('admin/agents/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.agents.create');
    Route::post('admin/agents/store', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.agents.store');
    Route::put('admin/agents/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.agents.update');
    Route::post('admin/agents/{user}/update-password', [App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('admin.agents.update-password');
    Route::delete('admin/agents/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.agents.destroy');
    // Admin Messages
    Route::get('admin/messages', [App\Http\Controllers\Admin\MessageController::class, 'index'])->name('admin.messages.index');
    Route::get('admin/messages/new-data', [App\Http\Controllers\Admin\MessageController::class, 'getNewMessagesData'])->name('admin.messages.new-data'); //for ajax for realtime data update
    Route::get('admin/messages/unread-dates', [App\Http\Controllers\Admin\MessageController::class, 'getUnreadMessageDates'])->name('admin.messages.unread-dates');
    // Admin Ranking
    Route::get('admin/ranking', [App\Http\Controllers\Admin\RankingController::class, 'index'])->name('admin.ranking.index');
    // Admin Knowledge Base
    Route::get('admin/knowledge-base', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'index'])->name('admin.knowledge-base.index');
    Route::post('admin/knowledge-base/categories', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'storeCategory'])->name('admin.knowledge-base.categories.store');
    Route::put('admin/knowledge-base/categories/{id}', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'updateCategory'])->name('admin.knowledge-base.categories.update');
    Route::delete('admin/knowledge-base/categories/{id}', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'destroyCategory'])->name('admin.knowledge-base.categories.destroy');
    Route::post('admin/knowledge-base/articles', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'storeArticle'])->name('admin.knowledge-base.articles.store');
    Route::put('admin/knowledge-base/articles/{id}', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'updateArticle'])->name('admin.knowledge-base.articles.update');
    Route::delete('admin/knowledge-base/articles/{id}', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'destroyArticle'])->name('admin.knowledge-base.articles.destroy');
    Route::post('admin/knowledge-base/faqs', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'storeFaq'])->name('admin.knowledge-base.faqs.store');
    Route::put('admin/knowledge-base/faqs/{id}', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'updateFaq'])->name('admin.knowledge-base.faqs.update');
    Route::delete('admin/knowledge-base/faqs/{id}', [App\Http\Controllers\Admin\KnowledgeBaseController::class, 'destroyFaq'])->name('admin.knowledge-base.faqs.destroy');
    // Admin Settings
    Route::get('admin/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('admin.settings');
    Route::post('admin/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('admin.settings.profile');
    Route::post('admin/settings/password', [\App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('admin.settings.password');
    Route::post('admin/settings/preferences', [\App\Http\Controllers\SettingsController::class, 'updatePreferences'])->name('admin.settings.preferences');
    Route::post('admin/settings/preferences/undo/{key}', [\App\Http\Controllers\SettingsController::class, 'undoSinglePreference'])->name('admin.settings.preferences.undo');
    Route::post('admin/settings/themes/save', [\App\Http\Controllers\SettingsController::class, 'saveTheme'])->name('admin.settings.themes.save');
    Route::post('admin/settings/themes/delete/{id}', [\App\Http\Controllers\SettingsController::class, 'deleteTheme'])->name('admin.settings.themes.delete');
});


