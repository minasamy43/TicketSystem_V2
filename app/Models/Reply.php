<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'admin_id', 'user_id', 'body', 'is_read', 'image'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Returns the display name and role of whoever posted this reply. */
    public function authorName(): string
    {
        if ($this->admin_id) {
            return ($this->admin->name ?? 'Admin') . ' (Support)';
        }
        return $this->user->name ?? 'User';
    }

    public function isFromAdmin(): bool
    {
        return !is_null($this->admin_id);
    }
}
