<?php

namespace App\Http\Controllers;

use App\Http\Requests\LiveTrackPointStoreRequest;
use App\Models\LiveTrack;
use App\Models\LiveTrackPoint;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LiveTrackController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewLiveTracksAdmin');
        // Admin view: show all tracks, including those created via API (user_id is null)
        $tracks = LiveTrack::query()
            ->with('user')
            ->latest('id')
            ->paginate(15);

        return view('live-tracks.index', [ 'tracks' => $tracks ]);
    }

    /**
     * API: Create a live track using bearer token auth (no session user/policy).
     */
    public function apiCreate(Request $request): JsonResponse
    {
        // Protected by auth:sanctum middleware.
        $publicId = Str::random(10);
        $writeKey = Str::password(32);

        $track = LiveTrack::query()->create([
            'user_id' => $request->user()?->id,
            'public_id' => $publicId,
            'write_key_hash' => Hash::make($writeKey),
            'title' => $request->string('title')->toString() ?: null,
            'started_at' => now(),
            'is_active' => true,
        ]);

        return response()->json([
            'publicId' => $track->public_id,
            'writeKey' => $writeKey,
            'ingestUrl' => route('api.live-tracks.points.store', $track->public_id),
            'pollUrl' => route('api.live-tracks.points.index', $track->public_id),
            'mapUrl' => route('live-tracks.show', $track->public_id),
        ]);
    }
    public function create(Request $request): JsonResponse
    {
        $this->authorize('create', LiveTrack::class);

        $publicId = Str::random(10);
        $writeKey = Str::password(32);
        $track = LiveTrack::query()->create([
            'user_id' => $request->user()?->id,
            'public_id' => $publicId,
            'write_key_hash' => Hash::make($writeKey),
            'title' => $request->string('title')->toString() ?: null,
            'started_at' => now(),
            'is_active' => true,
        ]);

        return response()->json([
            'publicId' => $track->public_id,
            'writeKey' => $writeKey,
            'ingestUrl' => route('live-tracks.points.store', $track->public_id),
            'pollUrl' => route('live-tracks.points.index', $track->public_id),
            'mapUrl' => route('live-tracks.show', $track->public_id),
        ]);
    }

    public function show(string $publicId): View
    {
        $track = LiveTrack::query()->where('public_id', $publicId)->firstOrFail();

        $activeTracks = $this->buildActiveTracksSnapshot();

        return view('live-tracks.show', [
            'track' => $track,
            'activeTracks' => $activeTracks,
        ]);
    }

    public function activeMap(): View
    {
        $activeTracks = $this->buildActiveTracksSnapshot();

        return view('live-tracks.active', [
            'activeTracks' => $activeTracks,
        ]);
    }

    /**
     * Public: return new points for all tracks (active and recently ended) since a given timestamp.
     */
    public function activePoints(Request $request): JsonResponse
    {
        $since = $request->date('since');

        // Fetch points for:
        // 1. Currently active tracks (is_active=true, no ended_at)
        // 2. Recently ended tracks (ended within last 24 hours)
        $points = LiveTrackPoint::query()
            ->whereHas('track', function ($q): void {
                $q->where(function ($query): void {
                    // Active tracks
                    $query->where('is_active', true)->whereNull('ended_at');
                })->orWhere(function ($query): void {
                    // Recently ended tracks (last 24 hours)
                    $query->where('is_active', false)
                        ->whereNotNull('ended_at')
                        ->where('ended_at', '>', now()->subHours(24));
                });
            })
            ->when($since !== null, function ($q) use ($since): void {
                $q->where('recorded_at', '>', $since);
            })
            ->orderBy('recorded_at')
            ->limit(1000)
            ->get(['live_track_id','latitude','longitude','recorded_at']);

        $trackIds = $points->pluck('live_track_id')->unique()->values();
        $tracks = LiveTrack::query()->with('user')->whereIn('id', $trackIds)->get()->keyBy('id');

        $grouped = [];
        foreach ($points as $p) {
            $tid = $p->live_track_id;
            $t = $tracks->get($tid);
            if (! isset($grouped[$tid])) {
                $grouped[$tid] = [
                    'publicId' => $t?->public_id,
                    'isActive' => $t?->is_active ?? false,
                    'user' => [
                        'id' => $t?->user?->id,
                        'name' => $t?->user?->name,
                    ],
                    'points' => [],
                ];
            }
            $grouped[$tid]['points'][] = [
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                't' => optional($p->recorded_at)?->toIso8601String(),
            ];
        }

        return response()->json([
            'tracks' => array_values($grouped),
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    /**
     * Build a snapshot of active tracks with recent points and user names.
     *
     * @param  bool  $enforceIdle  When true, requires last point within idle window.
     * @return array<int, array{publicId:string,user:array{id:int|null,name:?string},points:array<int,array{lat:float,lng:float}>}>
     */
    protected function buildActiveTracksSnapshot(bool $enforceIdle = false): array
    {
        $idleWindowSeconds = 300; // 5 minutes
        $now = now();

        // Get both active tracks and recently ended tracks
        $candidates = LiveTrack::query()
            ->where(function ($q): void {
                // Active tracks
                $q->where('is_active', true)->whereNull('ended_at');
            })->orWhere(function ($q): void {
                // Recently ended tracks (last 24 hours)
                $q->where('is_active', false)
                    ->whereNotNull('ended_at')
                    ->where('ended_at', '>', now()->subHours(24));
            })
            ->with('user')
            ->latest('id')
            ->limit(50) // safety cap
            ->get();

        $activeTracks = [];
        foreach ($candidates as $t) {
            $lastPoint = $t->points()->latest('recorded_at')->first();
            if ($enforceIdle && ($lastPoint === null || $lastPoint->recorded_at->lte($now->copy()->subSeconds($idleWindowSeconds)))) {
                continue; // idle when enforcing
            }

            // Fetch recent path (up to 200 points, ordered oldest->newest)
            $recentPoints = $t->points()
                ->latest('recorded_at')
                ->limit(200)
                ->get(['latitude','longitude','recorded_at'])
                ->sortBy('recorded_at')
                ->values();

            $activeTracks[] = [
                'publicId' => $t->public_id,
                'isActive' => $t->is_active,
                'user' => [
                    'id' => $t->user?->id,
                    'name' => $t->user?->name,
                ],
                'points' => $recentPoints->map(fn ($p) => [
                    'lat' => (float) $p->latitude,
                    'lng' => (float) $p->longitude,
                ])->all(),
            ];
        }

        return $activeTracks;
    }

    public function pointsIndex(Request $request, string $publicId): JsonResponse
    {
        $track = LiveTrack::query()->where('public_id', $publicId)->firstOrFail();

        $since = $request->date('since');
        $query = $track->points();
        if ($since !== null) {
            $query->where('recorded_at', '>', $since);
        }
        $points = $query->orderBy('recorded_at')->limit(500)->get(['latitude','longitude','accuracy_m','speed_mps','bearing_deg','recorded_at']);

        // Compute an "effective" active state: active AND not ended AND last point seen within idle window
        $idleWindowSeconds = 300; // 5 minutes
        $lastPointAt = $track->points()->latest('recorded_at')->value('recorded_at');
        $isEffectivelyActive = $track->is_active
            && $track->ended_at === null
            && ($lastPointAt !== null ? \Carbon\Carbon::parse($lastPointAt)->gt(now()->subSeconds($idleWindowSeconds)) : false);

        return response()->json([
            'track' => [
                'title' => $track->title,
                'isActive' => $isEffectivelyActive,
                'startedAt' => optional($track->started_at)?->toIso8601String(),
                'endedAt' => optional($track->ended_at)?->toIso8601String(),
                'lastPointAt' => $lastPointAt ? optional(\Carbon\Carbon::parse($lastPointAt))->toIso8601String() : null,
                'idleTimeoutSec' => $idleWindowSeconds,
            ],
            'points' => $points->map(fn (LiveTrackPoint $p) => [
                'lat' => $p->latitude,
                'lng' => $p->longitude,
                'accuracy' => $p->accuracy_m,
                'speed' => $p->speed_mps,
                'bearing' => $p->bearing_deg,
                't' => optional($p->recorded_at)?->toIso8601String(),
            ]),
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    public function pointsStore(LiveTrackPointStoreRequest $request, string $publicId): JsonResponse
    {
        $track = LiveTrack::query()->where('public_id', $publicId)->firstOrFail();

        // Require an authenticated user and ownership of the track if user-bound
        if ($request->user() === null) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if ($track->user_id !== null && $request->user()->id !== $track->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $writeKey = (string) $request->header('X-Track-Key', '');
        if (! Hash::check($writeKey, $track->write_key_hash)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validated();
        $point = $track->points()->create([
            'latitude' => $data['lat'],
            'longitude' => $data['lng'],
            'accuracy_m' => $data['accuracy'] ?? null,
            'speed_mps' => $data['speed'] ?? null,
            'bearing_deg' => $data['bearing'] ?? null,
            'recorded_at' => isset($data['t']) ? \Carbon\Carbon::parse($data['t']) : now(),
            'meta' => [
                'source' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            'id' => $point->id,
            'recordedAt' => $point->recorded_at->toIso8601String(),
        ], 201);
    }

    /**
     * End a live track using the secret write key.
     */
    public function end(Request $request, string $publicId): JsonResponse
    {
        $track = LiveTrack::query()->where('public_id', $publicId)->firstOrFail();

        $writeKey = (string) $request->header('X-Track-Key', '');
        if (! Hash::check($writeKey, $track->write_key_hash)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $track->forceFill([
            'is_active' => false,
            'ended_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Track ended',
            'endedAt' => optional($track->ended_at)?->toIso8601String(),
        ]);
    }

    /**
     * Admin: forcibly end a live track by id (web form).
     */
    public function adminEnd(Request $request, int $track): RedirectResponse
    {
        $this->authorize('viewLiveTracksAdmin');

        $t = LiveTrack::query()->where('id', $track)->firstOrFail();
        if (! $t->is_active && $t->ended_at !== null) {
            return back()->with('status','Track already ended');
        }

        $t->forceFill([
            'is_active' => false,
            'ended_at' => now(),
        ])->save();

        return back()->with('status','Track closed');
    }
}
