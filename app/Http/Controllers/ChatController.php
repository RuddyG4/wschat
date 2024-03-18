<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function show(Chat $chat)
    {
        $ownChat = $chat->users->contains(auth()->id());
        abort_unless($ownChat, 403);
        return Inertia::render('Chats/Index', [
            'chat' => $chat,
            'messages' => $chat->messages
        ]);
    }

    public function chatWith(User $user)
    {
        $localUser = auth()->user();

        $chat = $localUser->chats()->whereHas('users', function ($query) use ($user) {
            $query->where('chat_user.user_id', $user->id);
        })->first();

        if (!$chat) {
            $chat = Chat::create();
            $chat->users()->attach([
                $localUser->id,
                $user->id
            ]);
        }

        return redirect()->route('chat.show', $chat);
    }
}
