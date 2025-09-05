<?php

namespace App\Http\Controllers;

use App\Models\AiMessage;
use App\Models\Guidance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class AiGuidanceController extends Controller
{
    public function store(AiMessage $message): RedirectResponse
    {
    // Load the parent conversation + its user (original code referenced a non-existent 'conversation' rel on AiConversation)
    $conv = $message->conversation()->with('user')->first();
        if ($conv->user_id !== Auth::id()) {
            abort(403);
        }
        if ($message->role !== 'assistant') {
            return back()->with('status','Only assistant responses can be converted.');
        }
        // Custom title from request or derive from content
        $requestedTitle = trim(request('title',''));
        if ($requestedTitle !== '') {
            $title = substr($requestedTitle,0,80);
        } else {
            $raw = trim($message->content);
            $title = substr(preg_split('/[\.!?]\s/',$raw,2)[0] ?? substr($raw,0,60),0,80);
            if (strlen($title) < 5) {
                $title = 'AI Guidance Draft';
            }
        }
        $guidance = Guidance::create([
            'species_id' => null,
            'title' => $title,
            'content' => $message->content,
            'type' => 'best_practice',
            'active' => false,
            'status' => 'pending',
            'created_by' => Auth::id(),
            'metadata' => [
                'source' => 'ai',
                'ai_message_id' => $message->id,
                'conversation_id' => $conv->id,
            ],
        ]);
        return redirect()->route('guidances.index')->with('status','Guidance draft created (ID '.$guidance->id.').');
    }
}
