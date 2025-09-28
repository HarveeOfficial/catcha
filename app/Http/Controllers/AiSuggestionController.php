<?php

namespace App\Http\Controllers;

use App\Models\AiSuggestion;
use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AiSuggestionController extends Controller
{
    protected function authorizeCatch(FishCatch $catch): void
    {
        $u = Auth::user();
        if (! $u) {
            abort(401);
        }
        $isAdmin = ($u instanceof User) && method_exists($u, 'isAdmin') ? $u->isAdmin() : false;
        $isExpert = ($u instanceof User) && method_exists($u, 'isExpert') ? $u->isExpert() : false;
        if (! ($isAdmin || $isExpert) && $u->id !== $catch->user_id) {
            abort(403);
        }
    }

    public function showCatch(FishCatch $fishCatch)
    {
        $this->authorizeCatch($fishCatch);
        $rec = AiSuggestion::query()
            ->where('subject_type', 'fish_catches')
            ->where('subject_id', $fishCatch->id)
            ->where('scope', 'catch_show')
            ->first();

        if (! $rec) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'content' => $rec->content,
            'model' => $rec->model,
            'updated_at' => $rec->updated_at?->toIso8601String(),
        ]);
    }

    public function generateCatch(Request $request, FishCatch $fishCatch)
    {
        $this->authorizeCatch($fishCatch);

        $force = (bool) $request->boolean('force');
        $existing = AiSuggestion::query()
            ->where('subject_type', 'fish_catches')
            ->where('subject_id', $fishCatch->id)
            ->where('scope', 'catch_show')
            ->first();

        if ($existing && ! $force) {
            return response()->json([
                'cached' => true,
                'content' => $existing->content,
                'model' => $existing->model,
                'updated_at' => $existing->updated_at?->toIso8601String(),
            ]);
        }

        $apiKey = config('services.openai.key');
        if (! $apiKey) {
            return response()->json(['error' => 'AI service not configured'], 500);
        }
        $model = config('services.openai.model', 'gpt-4o-mini');
        $fallbackChain = array_values(array_unique([
            $model,
            'gpt-4o-mini',
            'gpt-4o-mini-1',
            'gpt-3.5-turbo',
            'gpt-3.5-turbo-0125',
        ]));
        $timeout = (int) config('services.openai.timeout', 30);

        $question = $this->buildCatchPrompt($fishCatch);

        $messages = [
            ['role' => 'system', 'content' => 'You are an assistant helping fishers with sustainable fishing, weather interpretation, and catch optimization. Be concise.'],
            ['role' => 'user', 'content' => $question],
        ];

        $answer = null;
        $usedModel = null;
        $lastError = null;
        $tried = [];
        $http = Http::withToken($apiKey)->timeout($timeout)->acceptJson();
        foreach ($fallbackChain as $candidate) {
            $tried[] = $candidate;
            $payload = [
                'model' => $candidate,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 400,
            ];
            $resp = $http->post('https://api.openai.com/v1/chat/completions', $payload);
            if ($resp->successful()) {
                $json = $resp->json();
                $answer = $json['choices'][0]['message']['content'] ?? null;
                if ($answer) {
                    $answer = preg_replace('/\*\*(.*?)\*\*/s', '$1', $answer);
                    $answer = preg_replace('/^#{1,6}\s*/m', '', $answer);
                }
                $usedModel = $candidate;
                break;
            } else {
                $err = $resp->json();
                $code = $err['error']['code'] ?? $err['error']['type'] ?? null;
                if (! in_array($code, ['model_not_found', 'invalid_model', 'not_found'])) {
                    $lastError = $err;
                    break;
                }
            }
        }

        if (! $answer) {
            return response()->json([
                'error' => 'AI request failed',
                'details' => app()->isLocal() || config('app.debug') ? $lastError : null,
                'models_tried' => $tried,
            ], 502);
        }

        $rec = $existing ?? new AiSuggestion([
            'subject_type' => 'fish_catches',
            'subject_id' => $fishCatch->id,
            'scope' => 'catch_show',
        ]);
        $rec->content = $answer;
        $rec->model = $usedModel;
        $rec->created_by = Auth::id();
        $rec->save();

        return response()->json([
            'cached' => false,
            'content' => $rec->content,
            'model' => $rec->model,
            'updated_at' => $rec->updated_at?->toIso8601String(),
        ]);
    }

    protected function buildCatchPrompt(FishCatch $c): string
    {
        $lines = [];
        $lines[] = 'Provide concise, actionable suggestions for this single catch. Focus strictly on the facts below.';
        $lines[] = 'Rules:';
        $lines[] = '- Ground every point in the data provided. Do not speculate beyond it.';
        $lines[] = "- If a factor is unknown (e.g., regulation, season), say 'insufficient data' rather than guessing.";
        $lines[] = '- Avoid legal claims or external rules unless explicitly present in the data.';
        $lines[] = "- When appropriate, add a line starting with 'Don't touch that:' to advise keeping something unchanged (also grounded in the data).";
        $lines[] = 'Format: 4-6 bullet points, <=160 chars each. No markdown headings or bold.';
        $lines[] = '';

        // Base catch data
        $lines[] = 'Catch Data:';
        $lines[] = 'Date/Time: '.optional($c->caught_at)->format('Y-m-d H:i');
        $lines[] = 'Species: '.($c->species?->common_name ?? 'N/A');
        $lines[] = 'Quantity (kg): '.(is_null($c->quantity) ? 'N/A' : (string) $c->quantity);
        $lines[] = 'Count: '.(is_null($c->count) ? 'N/A' : (string) $c->count);
        $lines[] = 'Location: '.($c->location ?? 'N/A');
        $lines[] = 'Gear: '.($c->gear_type ?? 'N/A');
        $w = $c->weather;
        if (is_array($w)) {
            $wLine = 'Weather: Temp '.($w['temperature_c'] ?? 'N/A').'C, Wind '.($w['wind_speed_kmh'] ?? 'N/A').' km/h';
            if (isset($w['wind_dir_deg'])) {
                $wLine .= ', Dir '.$w['wind_dir_deg'].'°';
            }
            $wLine .= ', Humidity '.($w['humidity_percent'] ?? 'N/A').'%';
            $lines[] = $wLine;
        }

        // Data-driven context (last 30 days)
        $lines[] = '';
        $lines[] = 'Recent Data (last 30 days):';
        $since = now()->subDays(30);

        // User aggregates
        $userAgg = FishCatch::query()
            ->where('user_id', $c->user_id)
            ->where('caught_at', '>=', $since)
            ->selectRaw('COUNT(*) as cnt, COALESCE(AVG(quantity),0) as avg_qty, COALESCE(AVG(`count`),0) as avg_count, COALESCE(SUM(quantity),0) as sum_qty')
            ->first();
        if ($userAgg) {
            $lines[] = 'User total catches: '.(int) $userAgg->cnt.'; avg qty: '.number_format((float) $userAgg->avg_qty, 2).' kg; avg count: '.number_format((float) $userAgg->avg_count, 2).'; total qty: '.number_format((float) $userAgg->sum_qty, 2).' kg';
        }

        // User + species aggregates
        if (! is_null($c->species_id)) {
            $userSpec = FishCatch::query()
                ->where('user_id', $c->user_id)
                ->where('species_id', $c->species_id)
                ->where('caught_at', '>=', $since)
                ->selectRaw('COUNT(*) as cnt, COALESCE(AVG(quantity),0) as avg_qty, COALESCE(AVG(`count`),0) as avg_count')
                ->first();
            $lines[] = 'User '.($c->species?->common_name ?? 'species').': samples '.(int) ($userSpec->cnt ?? 0).'; avg qty '.number_format((float) ($userSpec->avg_qty ?? 0), 2).' kg; avg count '.number_format((float) ($userSpec->avg_count ?? 0), 2);

            // Global species aggregates
            $globalSpec = FishCatch::query()
                ->where('species_id', $c->species_id)
                ->where('caught_at', '>=', $since)
                ->selectRaw('COUNT(*) as cnt, COALESCE(AVG(quantity),0) as avg_qty, COALESCE(AVG(`count`),0) as avg_count')
                ->first();
            $lines[] = 'All users '.($c->species?->common_name ?? 'species').': samples '.(int) ($globalSpec->cnt ?? 0).'; avg qty '.number_format((float) ($globalSpec->avg_qty ?? 0), 2).' kg; avg count '.number_format((float) ($globalSpec->avg_count ?? 0), 2);
        }

        // Nearby aggregates if coordinates exist
        if (! is_null($c->latitude) && ! is_null($c->longitude)) {
            $lat = (float) $c->latitude;
            $lon = (float) $c->longitude;
            $latDelta = 0.1; // ~11 km
            $cos = max(cos(deg2rad(max(min($lat, 89.9), -89.9))), 0.01);
            $lonDelta = 0.1 / $cos; // approx adjust with latitude
            $nearby = FishCatch::query()
                ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                ->whereBetween('longitude', [$lon - $lonDelta, $lon + $lonDelta])
                ->where('caught_at', '>=', $since)
                ->selectRaw('COUNT(*) as cnt, COALESCE(AVG(quantity),0) as avg_qty')
                ->first();
            $lines[] = 'Nearby (~10km) samples: '.(int) ($nearby->cnt ?? 0).'; avg qty '.number_format((float) ($nearby->avg_qty ?? 0), 2).' kg';
        }

        // Feedback info
        $fbCount = $c->feedbacks()->count();
        $fbApproved = $c->feedbacks()->where('approved', true)->count();
        $lines[] = 'Feedback: total '.(int) $fbCount.'; approved '.(int) $fbApproved;

        return implode("\n", $lines);
    }
}
