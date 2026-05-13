<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($ticket) {
            // Delete ticket images
            if ($ticket->images) {
                foreach ($ticket->images as $image) {
                    \Storage::disk('public')->delete($image);
                }
            }
            // Delete reply images
            foreach ($ticket->replies as $reply) {
                if ($reply->image) {
                    \Storage::disk('public')->delete($reply->image);
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
        'priority',
        'images',
        'has_admin_read',
        'has_user_read',
        'closed_by',
        'inprogress_by',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function inprogressBy()
    {
        return $this->belongsTo(User::class, 'inprogress_by');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class)->with('admin')->oldest();
    }
}
