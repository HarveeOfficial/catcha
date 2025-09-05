<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use Illuminate\Support\Facades\Auth;

class AiConversationController extends Controller
{
    public function index()
    {
        $convs = AiConversation::withCount('messages')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);
        return view('ai.conversations.index', compact('convs'));
    }

    public function show(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 403);
        $messages = $conversation->messages()->orderBy('id')->get();
        return view('ai.conversations.show', compact('conversation','messages'));
    }

    public function destroy(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 403);
        $conversation->delete();
        return redirect()->route('ai.conversations.index')->with('status','Conversation deleted');
    }
}
