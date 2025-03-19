<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Application\Chat\ChatMessageHandler;

class ChatMessageController extends Controller
{
    protected $handler;

    public function __construct(ChatMessageHandler $handler)
    {
        $this->handler = $handler;
    }

    public function index()
    {
        return response()->json($this->handler->getAll());
    }

    public function store(Request $request)
    {
        $data = $request->only(['category', 'title', 'body', 'tags']);
        $message = $this->handler->create($data);
        return response()->json($message);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['category', 'title', 'body', 'tags']);
        $message = $this->handler->update($id, $data);
        return response()->json($message);
    }

    public function destroy($id)
    {
        $this->handler->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
