<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Knowledge extends Model
{
    use HasFactory, SoftDeletes;

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
