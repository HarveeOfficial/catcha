<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use Illuminate\Support\Facades\Auth;

class AiConversationController extends Controller
{
    public function index()
    {
        $query = AiConversation::withCount('messages')
            ->where('user_id', Auth::id())
            ->latest();
        // For chat tabs: return lightweight JSON list when requested
        if (request()->wantsJson()) {
            return $query->limit(25)->get(['id', 'title', 'model', 'created_at']);
        }
        $convs = $query->paginate(15);

        return view('ai.conversations.index', compact('convs'));
    }

    public function show(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 403);
        $messages = $conversation->messages()->orderBy('id')->get();
        if (request()->wantsJson()) {
            return [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'model' => $conversation->model,
                'messages' => $messages->map(fn ($m) => [
                    'role' => $m->role,
                    'content' => $m->content,
                ]),
            ];
        }

        return view('ai.conversations.show', compact('conversation', 'messages'));
    }

    public function destroy(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 403);
        $conversation->delete();
        if (request()->wantsJson()) {
            return response()->noContent(); // 204
        }

        return redirect()->route('ai.conversations.index')->with('status', 'Conversation deleted');
    }
}
