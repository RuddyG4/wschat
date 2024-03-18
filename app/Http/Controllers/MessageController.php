<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function sendMessage(Request $request, Chat $chat)
    {
        $message = $chat->messages()->create([
            'content' => $request->message,
            'user_id' => $request->user()->id
        ])->load('user');

        broadcast(new \App\Events\MessageSent($message))->toOthers();
        // return response(['message' => $message], 200);
    }
}
