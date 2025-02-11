<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
    ];

    // カテゴリーとの多対多関係
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'knowledge_category');
    }

    // タグとの多対多関係
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'knowledge_tag');
    }
}
