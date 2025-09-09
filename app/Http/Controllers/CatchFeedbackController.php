<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\CatchFeedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CatchFeedbackLike;

class CatchFeedbackController extends Controller
{
    public function index(FishCatch $fishCatch)
    {
        $fishCatch->load(['species','feedbacks.expert','feedbacks.likes']);
        $feedbacks = $fishCatch->feedbacks()->with('likes')->withCount('likes')->latest()->get();
        return view('catches.feedback', [
            'catch' => $fishCatch,
            'feedbacks' => $feedbacks,
        ]);
    }

    public function store(Request $request, FishCatch $fishCatch)
    {
    /** @var User $user */
    $user = Auth::user();
    if (!$user->isExpert() && !$user->isAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'approved' => 'nullable|boolean',
            'comments' => 'nullable|string',
            'flags' => 'nullable|array'
        ]);
        $data['approved'] = $request->boolean('approved');
        $data['expert_id'] = Auth::id();
        $data['fish_catch_id'] = $fishCatch->id;
        CatchFeedback::create($data);
        return redirect()->route('catches.feedback.index', $fishCatch)->with('status','Feedback added');
    }

    public function destroy(CatchFeedback $feedback)
    {
    /** @var User $user */
    $user = Auth::user();
    if (Auth::id() !== $feedback->expert_id) {
            abort(403);
        }
        $catchId = $feedback->fish_catch_id;
        $feedback->delete();
        return redirect()->route('catches.feedback.index', $catchId)->with('status','Feedback removed');
    }

    public function update(Request $request, CatchFeedback $feedback)
    {
        /** @var User $user */
        $user = Auth::user();
        // Only the author expert or an admin can edit a feedback
        if ($user->id !== $feedback->expert_id && ! $user->isAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'approved' => 'nullable|boolean',
            'comments' => 'nullable|string',
            'flags' => 'nullable|array',
        ]);
        $data['approved'] = $request->boolean('approved');
        $feedback->update($data);
        return back()->with('status','Feedback updated');
    }

    public function like(CatchFeedback $feedback)
    {
        $userId = Auth::id();
        CatchFeedbackLike::firstOrCreate([
            'catch_feedback_id' => $feedback->id,
            'user_id' => $userId
        ]);
        return back()->with('status','Liked');
    }

    public function unlike(CatchFeedback $feedback)
    {
        CatchFeedbackLike::where('catch_feedback_id',$feedback->id)->where('user_id',Auth::id())->delete();
        return back()->with('status','Unliked');
    }
}
