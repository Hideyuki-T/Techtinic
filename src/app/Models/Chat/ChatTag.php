<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatTag extends Model
{
    protected $table = 'chat_tags';

    protected $fillable = ['name'];

    public function knowledges()
    {
        return $this->belongsToMany(ChatKnowledge::class, 'chat_knowledge_tag', 'tag_id', 'knowledge_id');
    }
}
