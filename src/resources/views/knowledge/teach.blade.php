@extends('layouts.app')

@section('title', 'Techtinic Knowledge Registration')

@section('content')
    <div style="margin-bottom: 10px;">
        <a href="/chat" class="btn">チャット画面に戻る</a>
    </div>

    <h1>Techtinic に知識を教える</h1>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    <form action="/teach" method="POST">
        @csrf
        <div class="form-group">
            <label for="category">カテゴリー</label>
            <input type="text" name="category" id="category" placeholder="例: dockerコマンド" required>
        </div>
        <div class="form-group">
            <label for="title">タイトル</label>
            <input type="text" name="title" id="title" placeholder="例: 起動済のコンテナ一覧の表示" required>
        </div>
        <div class="form-group">
            <label for="content">本文</label>
            <textarea name="content" id="content" rows="4" placeholder="例: docker ps と入力して、起動中のコンテナ一覧を表示する" required></textarea>
        </div>

        <div class="form-group">
            <label>既存のタグから選択 (複数選択可)</label>
            <div id="existing_tags">
                @foreach($existingTags as $tag)
                    <label>
                        <input type="checkbox" name="existing_tags[]" value="{{ $tag->id }}">
                        {{ $tag->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label for="new_tags">新しいタグ (カンマ区切りで入力)</label>
            <input type="text" name="new_tags" id="new_tags" placeholder="例: docker, コンテナ, 状態確認">
        </div>

        <button type="submit">知識を登録する</button>
    </form>
@endsection
