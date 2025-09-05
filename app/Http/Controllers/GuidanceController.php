<?php

namespace App\Http\Controllers;

use App\Models\Guidance;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuidanceController extends Controller
{
    public function index()
    {
        $query = Guidance::with('species');
    // Visibility rules:
    // - Admin: all records
    // - Expert/Fisher: approved & active OR own records (any status)
    // - Others (shouldn't exist): approved & active
        $user = Auth::user();
    $role = $user->role ?? null;
    if ($role === 'admin') {
            // no filter
    } elseif (in_array($role, ['expert','fisher'])) {
            $query->where(function($q) use ($user) {
                $q->where(function($qq){ $qq->where('status','approved')->where('active', true); })
                  ->orWhere('created_by', $user->id);
            });
        } else {
            $query->where('status','approved')->where('active', true);
        }
        $guidances = $query->latest()->paginate(20);
        return view('guidances.index', compact('guidances'));
    }

    public function create()
    {
    $user = Auth::user();
    $role = $user->role ?? null;
    abort_unless(in_array($role, ['admin','expert','fisher']), 403);
        $species = Species::orderBy('common_name')->get();
        return view('guidances.create', compact('species'));
    }

    public function store(Request $request)
    {
    $user = Auth::user();
    $role = $user->role ?? null;
    abort_unless(in_array($role, ['admin','expert','fisher']), 403);
        $data = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:regulation,best_practice,sustainability_tip,alert',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from'
        ]);
        // Always create as pending inactive until admin approval
        $data['active'] = false;
        $data['status'] = 'pending';
    $data['created_by'] = Auth::id();
    Guidance::create($data);
        return redirect()->route('guidances.index')->with('status','Guidance submitted and pending approval');
    }

    public function show(Guidance $guidance)
    {
        $guidance->load('species');
        $user = Auth::user();
    $role = $user->role ?? null;
        if ($guidance->status !== 'approved') {
            abort_unless($user && ($role === 'admin' || ($guidance->created_by === $user->id)), 403);
        }
        return view('guidances.show', compact('guidance'));
    }

    public function edit(Guidance $guidance)
    {
    $user = Auth::user();
    $role = $user->role ?? null;
    // Admin can edit any. Creator (any role) can edit if not approved. No one edits after approval except admin.
    abort_unless($user && ($role === 'admin' || ($guidance->created_by === $user->id && $guidance->status !== 'approved')), 403);
        $species = Species::orderBy('common_name')->get();
        return view('guidances.edit', compact('guidance','species'));
    }

    public function update(Request $request, Guidance $guidance)
    {
    $user = Auth::user();
    $role = $user->role ?? null;
    abort_unless($user && ($role === 'admin' || ($guidance->created_by === $user->id && $guidance->status !== 'approved')), 403);
        $data = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:regulation,best_practice,sustainability_tip,alert',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from'
        ]);
        // Editing resets to pending if previously rejected? Keep current status unless approved.
        if ($guidance->status !== 'approved') {
            $data['status'] = 'pending';
            $data['active'] = false; // stays inactive until approved
        }
        $guidance->update($data);
        return redirect()->route('guidances.show',$guidance)->with('status','Guidance updated'.($guidance->status==='pending' ? ' and pending approval':'') );
    }

    public function approve(Guidance $guidance)
    {
        $user = Auth::user();
    $role = $user->role ?? null;
    abort_unless($role === 'admin', 403);
        $guidance->update([
            'status' => 'approved',
            'active' => true,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejected_reason' => null
        ]);
        return redirect()->route('guidances.show',$guidance)->with('status','Guidance approved');
    }

    public function reject(Request $request, Guidance $guidance)
    {
        $user = Auth::user();
    $role = $user->role ?? null;
    abort_unless($role === 'admin', 403);
        $data = $request->validate([
            'reason' => 'nullable|string|max:1000'
        ]);
        $guidance->update([
            'status' => 'rejected',
            'active' => false,
            'approved_by' => $user->id,
            'approved_at' => null,
            'rejected_reason' => $data['reason'] ?? null
        ]);
        return redirect()->route('guidances.show',$guidance)->with('status','Guidance rejected');
    }

    public function destroy(Guidance $guidance)
    {
    $user = Auth::user();
    $role = $user->role ?? null;
    abort_unless($role === 'admin' || ($guidance->created_by === $user->id && $guidance->status !== 'approved'), 403);
    $guidance->delete();
        return redirect()->route('guidances.index')->with('status','Guidance deleted');
    }
}
