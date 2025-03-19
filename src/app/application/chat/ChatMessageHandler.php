<?php

namespace App\Application\Chat;

use App\Infrastructure\Chat\EloquentChatMessageRepository;
use App\Domain\Chat\ChatMessage;

class ChatMessageHandler
{
    protected $repository;

    public function __construct(EloquentChatMessageRepository $repository)
    {
        $this->repository = $repository;
    }

    // 新規作成処理
    public function create(array $data)
    {
        // 必要に応じてドメインモデルへ変換
        $chatMessage = new ChatMessage($data['category'], $data['title'], $data['body'], $data['tags']);
        return $this->repository->save($chatMessage);
    }

    // 更新処理
    public function update($id, array $data)
    {
        $chatMessage = $this->repository->find($id);
        // ドメイン側の更新メソッドがあるなら呼び出す
        // $chatMessage->updateData($data);
        // ここでは簡略化して、リポジトリ経由で update を実施
        return $this->repository->update($chatMessage, $data);
    }

    // 削除処理
    public function delete($id)
    {
        $chatMessage = $this->repository->find($id);
        return $this->repository->delete($chatMessage);
    }

    // 一覧取得処理
    public function getAll()
    {
        return $this->repository->all();
    }
}
