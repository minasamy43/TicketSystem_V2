<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbCategory extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'icon', 'description'];

    public function articles()
    {
        return $this->hasMany(KbArticle::class);
    }
}
