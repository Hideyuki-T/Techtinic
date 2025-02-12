<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Knowledge;
use App\Models\Category;
use App\Models\Tag;

class KnowledgeController extends Controller
{
    // GETリクエスト: 知識登録フォームを表示
    public function create()
    {
        // 既存のカテゴリーを渡す場合（必要なら）
        $categories = Category::all();
        return view('knowledge.teach', compact('categories'));
    }

    // POSTリクエスト: フォーム送信後の知識登録処理
    public function store(Request $request)
    {
        // バリデーション（簡易例）
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'tags'     => 'nullable|string',
        ]);

        // カテゴリーの処理（既存カテゴリーの利用または新規作成）
        $category = Category::firstOrCreate(['name' => $validated['category']]);

        // 知識の保存
        $knowledge = Knowledge::create([
            'title'   => $validated['title'],
            'content' => $validated['content'],
        ]);

        // カテゴリーとの関連付け
        $knowledge->categories()->attach($category->id);

        // タグの処理
        $tagInput = $validated['tags'];
        if (empty(trim($tagInput))) {
            $tagInput = $category->name;
        }
        // タグを小文字に変換して配列に
        $tagNames = array_map(function($tagName) {
            return strtolower(trim($tagName));
        }, explode(',', $tagInput));

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $knowledge->tags()->attach($tag->id);
            }
        }

        // 登録完了後、フォームに戻る
        return redirect('/teach')->with('success', '知識が登録されました！');
    }
}
