<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatchController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isExpert() || $user->isAdmin()) {
            $query = FishCatch::with(['species', 'user'])->withCount('feedbacks')->latest('caught_at');
            if ($request->filled('fisher')) {
                $term = $request->input('fisher');
                $query->whereHas('user', function ($q) use ($term) {
                    $q->where('name', 'like', '%'.$term.'%');
                });
            }
            $catches = $query->paginate(20)->appends($request->only('fisher'));
        } else {
            $catches = FishCatch::with(['species'])
                ->where('user_id', $user->id)
                ->latest('caught_at')
                ->paginate(15);
        }

        return view('catches.index', compact('catches'));
    }

    public function create()
    {
        $species = Species::orderBy('common_name')->get();

        return view('catches.create', compact('species'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            'location' => 'nullable|string|max:150',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'caught_at' => 'required|date',
            'quantity' => 'nullable|numeric|min:0',
            'count' => 'nullable|integer|min:0',
            'avg_size_cm' => 'nullable|numeric|min:0',
            'gear_type' => 'nullable|string|max:100',
            'vessel_name' => 'nullable|string|max:150',
            'environmental_data' => 'nullable|array',
            'notes' => 'nullable|array',
        ]);
        $data['user_id'] = Auth::id();
        FishCatch::create($data);

        return redirect()->route('catches.index')->with('status', 'Catch recorded');
    }

    public function show(FishCatch $fishCatch)
    {
        $user = Auth::user();
        if (! ($user->isExpert() || $user->isAdmin()) && $fishCatch->user_id !== $user->id) {
            abort(403);
        }
        $fishCatch->loadMissing(['species', 'user', 'feedbacks.expert', 'feedbacks.likes']);
        $feedbacks = $fishCatch->feedbacks()->with('likes')->withCount('likes')->latest()->get();

        return view('catches.show', ['catch' => $fishCatch, 'feedbacks' => $feedbacks]);
    }
}
