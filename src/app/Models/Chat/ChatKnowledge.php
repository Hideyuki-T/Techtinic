<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatKnowledge extends Model
{
    use SoftDeletes;

    protected $table = 'chat_knowledge';

    protected $fillable = ['title', 'content'];

    public function tags()
    {
        return $this->belongsToMany(ChatTag::class, 'chat_knowledge_tag', 'knowledge_id', 'tag_id');
    }

    public function categories()
    {
        return $this->belongsToMany(ChatCategory::class, 'chat_knowledge_category', 'knowledge_id', 'category_id');
    }
}
