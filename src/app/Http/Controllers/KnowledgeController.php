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
        $categories = Category::all();
        $existingTags = Tag::orderBy('name')->get();
        // ビュー名が「knowledge.teach」になっているか確認する
        return view('knowledge.teach', compact('categories', 'existingTags'));
    }

    // POSTリクエスト: フォーム送信後の知識登録処理
    public function store(Request $request)
    {
        // バリデーション（必要に応じて調整）
        $validated = $request->validate([
            'category'      => 'required|string|max:255',
            'title'         => 'required|string|max:255',
            'content'       => 'required|string',
            'existing_tags' => 'nullable|array',
            'new_tags'      => 'nullable|string',
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

        // 既存タグの処理
        if (!empty($validated['existing_tags'])) {
            foreach ($validated['existing_tags'] as $tagId) {
                $knowledge->tags()->syncWithoutDetaching($tagId);
            }
        }

        // 新規タグの処理
        if ($request->filled('new_tags')) {
            $newTagNames = array_filter(array_map(function($tagName) {
                return strtolower(trim($tagName));
            }, explode(',', $validated['new_tags'])));

            foreach ($newTagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $knowledge->tags()->syncWithoutDetaching($tag->id);
            }
        }

        return redirect('/teach')->with('success', '知識が登録されました！');
    }
}
