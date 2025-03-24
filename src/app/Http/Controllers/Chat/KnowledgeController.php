<?php

namespace App\Http\Controllers\Chat;

use App\Models\Category;
use App\Models\Knowledge;
use App\Models\Tag;
use App\Services\SystemStatusService;
use Exception;
use Illuminate\Http\Request;

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

        return redirect('/teach')->with('success', '登録されました！');
    }

    // DELETEリクエスト: 知識情報の削除（ソフトデリート）
    public function destroy(Request $request, $id)
    {
        // オンライン状態かどうかのチェック
        if (!SystemStatusService::isOnline()) {
            return response()->json([
                'error' => '今は、オフラインなので削除処理を実行できません。'
            ], 403);
        }

        $knowledge = Knowledge::findOrFail($id);
        $knowledge->delete(); // ソフトデリート実施

        return response()->json([
            'success' => '知識情報をソフトデリートしたよ。'
        ]);
    }

    // PUTリクエスト: 知識情報の更新処理
    public function update(Request $request, $id)
    {
        // バリデーション（store とほぼ同じルール）
        $validated = $request->validate([
            'category'      => 'required|string|max:255',
            'title'         => 'required|string|max:255',
            'content'       => 'required|string',
            'existing_tags' => 'nullable|array',
            'new_tags'      => 'nullable|string',
        ]);

        // 対象の知識データを取得
        $knowledge = Knowledge::findOrFail($id);

        // 知識データの更新
        $knowledge->title = $validated['title'];
        $knowledge->content = $validated['content'];
        $knowledge->save();

        // カテゴリーの更新
        $category = Category::firstOrCreate(['name' => $validated['category']]);
        // カテゴリーとの関連付けは1件だけ更新する例（必要に応じて複数に変更可能）
        $knowledge->categories()->sync([$category->id]);

        // 既存タグの更新
        if (!empty($validated['existing_tags'])) {
            // 既存タグのID配列で関連付けを更新（他のタグは一旦削除）
            $knowledge->tags()->sync($validated['existing_tags']);
        } else {
            // 既存タグが空の場合は全て解除
            $knowledge->tags()->detach();
        }

        // 新規タグの処理
        if ($request->filled('new_tags')) {
            $newTagNames = array_filter(array_map(function($tagName) {
                return strtolower(trim($tagName));
            }, explode(',', $validated['new_tags'])));

            foreach ($newTagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                // 新規タグを既存のタグとの関連に追加（重複防止）
                $knowledge->tags()->syncWithoutDetaching($tag->id);
            }
        }

        return response()->json([
            'success'   => '知識情報が更新されました。',
            'knowledge' => $knowledge
        ]);
    }

    /**
     * DELETEリクエスト: 指定したタグを削除する処理
     *
     * @param int $id 削除するタグのID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyTag($id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json(['error' => 'Tag not found.'], 404);
        }

        try {
            $tag->delete();
            return response()->json(['message' => 'Tag deleted successfully.']);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete tag.',
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
