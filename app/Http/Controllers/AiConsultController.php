<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\AiConversation;
use App\Models\AiMessage;

class AiConsultController extends Controller
{
    /**
     * Handle an AI consultation request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:2000',
            'history' => 'array', // optional previous messages
            'history.*.role' => 'required_with:history|string|in:user,assistant,system',
            'history.*.content' => 'required_with:history|string|max:4000',
            'conversation_id' => 'nullable|integer|exists:ai_conversations,id',
            'save' => 'sometimes|boolean',
            'model' => 'sometimes|string|max:100'
        ]);

        // Domain restriction: only allow fishing / marine sustainability related questions.
        $questionLower = mb_strtolower($data['question']);
        $keywords = [
            'fish','fishing','catch','catches','species','gear','net','line','hook','trawl','trap','bycatch','quota','size limit','regulation','season','marine','ocean','boat','vessel','weather','wind','tide','current','safety','sustainable','sustainability','compliance'
        ];
        $relevant = false;
        foreach ($keywords as $kw) {
            if (str_contains($questionLower, $kw)) { $relevant = true; break; }
        }
        if (!$relevant) {
            $allowed = ['catches','species identification','gear optimization','weather safety','sustainability','regulations','size limits','bycatch reduction'];
            return response()->json([
                'question' => $data['question'],
                'answer' => 'This assistant is dedicated to small-scale fishing and marine sustainability topics. Please rephrase your question to focus on: '.implode(', ', $allowed).'. For example: "How can I reduce bycatch with a gill net?" or "What size limits apply to my catch?"',
                'model' => 'domain-filter',
                'usage' => null,
                'models_tried' => ['domain-filter'],
                'conversation_id' => null,
                'notice' => 'out_of_scope'
            ], 200);
        }

        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return response()->json(['error' => 'AI service not configured'], 500);
        }

        // Determine preferred model (request override -> config -> default)
        $model = $data['model'] ?? config('services.openai.model', 'gpt-4o-mini');
        // Ordered fallback list (will try in sequence until one succeeds)
        $fallbackChain = array_values(array_unique([
            $model,
            'gpt-4o-mini',
            'gpt-4o-mini-1',
            'gpt-3.5-turbo',
            'gpt-3.5-turbo-0125'
        ]));
        $timeout = (int) config('services.openai.timeout', 30);

        try {
            $messages = [];
            $messages[] = [
                'role' => 'system',
                'content' => 'You are an assistant helping fishers with sustainable fishing, weather interpretation, and catch optimization. Be concise.'
            ];
            if (!empty($data['history'])) {
                // Append sanitized history (skip system to avoid injection except first)
                foreach ($data['history'] as $h) {
                    if ($h['role'] === 'system') continue; // ignore client-sent system
                    $messages[] = [
                        'role' => $h['role'],
                        'content' => $h['content']
                    ];
                }
            }
            $messages[] = [
                'role' => 'user',
                'content' => $data['question']
            ];
            $http = Http::withToken($apiKey)
                ->timeout($timeout)
                ->acceptJson();

            $answer = null; $usedModel = null; $usage = null; $lastError = null; $tried = [];
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
                    // Remove markdown bold markers and headings per user request
                    if ($answer) {
                        $answer = preg_replace('/\*\*(.*?)\*\*/s', '$1', $answer); // bold
                        $answer = preg_replace('/^#{1,6}\s*/m', '', $answer); // headings
                    }
                    $usage = $json['usage'] ?? null;
                    $usedModel = $candidate;
                    break;
                } else {
                    $lastError = $resp->json();
                    $code = $lastError['error']['code'] ?? $lastError['error']['type'] ?? null;
                    // Non-model errors (auth, rate limit) -> stop immediately
                    if (!in_array($code, ['model_not_found','invalid_model','not_found'])) {
                        break;
                    }
                }
            }

            // If still no answer, return error diagnostics
            if (!$answer) {
                return response()->json([
                    'error' => 'AI request failed',
                    'details' => app()->isLocal() || config('app.debug') ? $lastError : null,
                    'models_tried' => $tried,
                ], 502);
            }
            $conversationId = null;
            if (($data['save'] ?? false) && Auth::check()) {
                $user = Auth::user();
                $conv = null;
                if (!empty($data['conversation_id'])) {
                    $conv = AiConversation::where('id',$data['conversation_id'])->where('user_id',$user->id)->first();
                }
                if (!$conv) {
                    $conv = AiConversation::create([
                        'user_id' => $user->id,
                        'title' => substr($data['question'],0,80),
                        'model' => $usedModel,
                    ]);
                }
                $conversationId = $conv->id;
                // Persist user question and assistant answer
                AiMessage::create([
                    'ai_conversation_id' => $conv->id,
                    'role' => 'user',
                    'content' => $data['question'],
                ]);
                AiMessage::create([
                    'ai_conversation_id' => $conv->id,
                    'role' => 'assistant',
                    'content' => $answer,
                ]);
            }
            return response()->json([
                'question' => $data['question'],
                'answer' => $answer,
                'model' => $usedModel,
                'usage' => $usage,
                'models_tried' => $tried,
                'conversation_id' => $conversationId,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI consult error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Exception contacting AI service'], 500);
        }
    }
}
