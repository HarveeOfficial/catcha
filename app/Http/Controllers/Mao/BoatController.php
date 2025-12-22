<?php

namespace App\Http\Controllers\Mao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mao\StoreBoatRequest;
use App\Http\Requests\Mao\UpdateBoatRequest;
use App\Models\Boat;
use App\Services\PsgcLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BoatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $boats = Boat::query()
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Boat::count(),
            'active' => Boat::where('status', 'active')->count(),
            'expired' => Boat::where('status', 'expired')->orWhere(function ($q) {
                $q->where('status', 'active')->whereDate('expiry_date', '<', now());
            })->count(),
            'motorized' => Boat::where('boat_type', 'motorized')->count(),
            'non_motorized' => Boat::where('boat_type', 'non-motorized')->count(),
        ];

        return view('mao.boats.index', compact('boats', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(PsgcLocationService $locationService): View
    {
        $regions = $locationService->getRegions();

        return view('mao.boats.create', compact('regions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoatRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $boat = Boat::create($data);

        return redirect()
            ->route('mao.boats.show', $boat)
            ->with('success', 'Boat registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Boat $boat): View
    {
        return view('mao.boats.show', compact('boat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Boat $boat, PsgcLocationService $locationService): View
    {
        $regions = $locationService->getRegions();

        return view('mao.boats.edit', compact('boat', 'regions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoatRequest $request, Boat $boat): RedirectResponse
    {
        $boat->update($request->validated());

        return redirect()
            ->route('mao.boats.show', $boat)
            ->with('success', 'Boat updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Boat $boat): RedirectResponse
    {
        $boat->delete();

        return redirect()
            ->route('mao.boats.index')
            ->with('success', 'Boat deleted successfully.');
    }
}
