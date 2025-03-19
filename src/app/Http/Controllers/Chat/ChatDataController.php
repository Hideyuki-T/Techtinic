<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatDataController extends Controller
{
    public function index()
    {
        $tags = DB::table('chat_tags')->get();
        $categories = DB::table('chat_categories')->get();
        $knowledges = DB::table('chat_knowledge')->get();
        $knowledgeTags = DB::table('chat_knowledge_tag')->get();
        $knowledgeCategories = DB::table('chat_knowledge_category')->get();

        return response()->json([
            'tags' => $tags,
            'categories' => $categories,
            'knowledges' => $knowledges,
            'knowledgeTags' => $knowledgeTags,
            'knowledgeCategories' => $knowledgeCategories,
        ]);
    }
}
