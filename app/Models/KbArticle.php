<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    use HasFactory;

    protected $fillable = ['kb_category_id', 'slug', 'title', 'content'];

    public function category()
    {
        return $this->belongsTo(KbCategory::class, 'kb_category_id');
    }
}
