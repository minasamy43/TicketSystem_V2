<?php

use App\Http\Controllers\User\KnowledgeBaseController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
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
Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('tickets/unread-counts', [App\Http\Controllers\Admin\TicketController::class, 'getUnreadCounts'])->name('tickets.unread-counts')->middleware('auth');
Route::get('admin/tickets/new-data', [App\Http\Controllers\Admin\TicketController::class, 'getNewTicketsData'])->name('admin.tickets.new-data')->middleware(['auth', 'admin']);
// Knowledge Base routes accessible to both User and Admin (for Preview only)
Route::middleware('auth')->group(function () {
    Route::get('user/knowledge-base', [App\Http\Controllers\User\KnowledgeBaseController::class, 'index'])->name('knowledge.base');
    Route::get('user/knowledge-base/category/{slug}', [App\Http\Controllers\User\KnowledgeBaseController::class, 'showCategory'])->name('knowledge.category');
    Route::get('user/knowledge-base/article/{slug}', [App\Http\Controllers\User\KnowledgeBaseController::class, 'showArticle'])->name('knowledge.article');
});

//User dashboard
Route::middleware(['auth', 'user'])->group(function () {
    Route::get('user/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    Route::get('user/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('user/tickets/store', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('user/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('user/tickets/{id}/chat-data', [TicketController::class, 'getChatData'])->name('tickets.chat-data');
    Route::post('user/tickets/{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::post('user/tickets/{id}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::delete('user/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
});


// Admin dashboard
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('admin/tickets', [App\Http\Controllers\Admin\TicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('admin/tickets/{id}', [App\Http\Controllers\Admin\TicketController::class, 'show'])->name('admin.tickets.show');
    Route::post('admin/tickets/{id}/status', [App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('admin.tickets.status');
    Route::post('admin/tickets/{id}/comment', [App\Http\Controllers\Admin\TicketController::class, 'storeComment'])->name('admin.tickets.comment');
    Route::get('admin/tickets/{id}/chat-data', [App\Http\Controllers\Admin\TicketController::class, 'getChatData'])->name('admin.tickets.chat-data');
    // admin user management
    Route::get('admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
    Route::post('admin/users/store', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::put('admin/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::post('admin/users/{user}/update-password', [App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::delete('admin/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');

    // Admin Messages
    Route::get('admin/messages', [App\Http\Controllers\Admin\MessageController::class, 'index'])->name('admin.messages.index');
    Route::get('admin/messages/new-data', [App\Http\Controllers\Admin\MessageController::class, 'getNewMessagesData'])->name('admin.messages.new-data');

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
});


