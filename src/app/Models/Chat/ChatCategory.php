<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatCategory extends Model
{
    protected $table = 'chat_categories';

    protected $fillable = ['name'];

    public function knowledges()
    {
        return $this->belongsToMany(ChatKnowledge::class, 'chat_knowledge_category', 'category_id', 'knowledge_id');
    }
}
