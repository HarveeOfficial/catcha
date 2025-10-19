<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'model' => 'sometimes|string|max:100',
            'provider' => 'sometimes|string|in:openai,gemini',
        ]);

        // Domain restriction: only allow fishing / marine sustainability related questions.
        $questionLower = mb_strtolower($data['question']);
        $keywords = [
            'fish', 'fishing', 'catch', 'catches', 'species', 'gear', 'net', 'line', 'hook', 'trawl', 'trap', 'bycatch', 'quota', 'size limit', 'regulation', 'season', 'marine', 'ocean', 'boat', 'vessel', 'weather', 'wind', 'tide', 'current', 'safety', 'sustainable', 'sustainability', 'compliance',
        ];
        $relevant = false;
        foreach ($keywords as $kw) {
            if (str_contains($questionLower, $kw)) {
                $relevant = true;
                break;
            }
        }
        if (! $relevant) {
            $allowed = ['catches', 'species identification', 'gear optimization', 'weather safety', 'sustainability', 'regulations', 'size limits', 'bycatch reduction'];

            return response()->json([
                'question' => $data['question'],
                'answer' => 'This assistant is dedicated to small-scale fishing and marine sustainability topics. Please rephrase your question to focus on: '.implode(', ', $allowed).'. For example: "How can I reduce bycatch with a gill net?" or "What size limits apply to my catch?"',
                'model' => 'domain-filter',
                'usage' => null,
                'models_tried' => ['domain-filter'],
                'conversation_id' => null,
                'notice' => 'out_of_scope',
                'provider' => null,
            ], 200);
        }

        // Determine AI provider (default: openai)
        $provider = $data['provider'] ?? 'openai';
        $availableProviders = AiProviderFactory::getAvailableProviders();
        if (! isset($availableProviders[$provider])) {
            return response()->json(['error' => 'AI provider not configured or invalid'], 500);
        }

        try {
            $messages = [];
            $messages[] = [
                'role' => 'system',
                'content' => 'You are an assistant helping fishers with sustainable fishing, weather interpretation, and catch optimization. Be concise.',
            ];
            if (! empty($data['history'])) {
                // Append sanitized history (skip system to avoid injection except first)
                foreach ($data['history'] as $h) {
                    if ($h['role'] === 'system') {
                        continue;
                    } // ignore client-sent system
                    $messages[] = [
                        'role' => $h['role'],
                        'content' => $h['content'],
                    ];
                }
            }
            $messages[] = [
                'role' => 'user',
                'content' => $data['question'],
            ];

            $aiProvider = AiProviderFactory::make($provider);
            $result = $aiProvider->chat($messages);
            $answer = $result['answer'];
            $usedModel = $result['model'];
            $usage = $result['usage'];

            $conversationId = null;
            if (($data['save'] ?? false) && Auth::check()) {
                $user = Auth::user();
                $conv = null;
                if (! empty($data['conversation_id'])) {
                    $conv = AiConversation::where('id', $data['conversation_id'])->where('user_id', $user->id)->first();
                }
                if (! $conv) {
                    $conv = AiConversation::create([
                        'user_id' => $user->id,
                        'title' => substr($data['question'], 0, 80),
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
                'models_tried' => [$usedModel],
                'conversation_id' => $conversationId,
                'provider' => $provider,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI consult error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'provider' => $provider,
                'exception' => get_class($e),
            ]);

            return response()->json([
                'error' => 'Exception contacting AI service: '.$e->getMessage(),
                'provider' => $provider,
            ], 500);
        }
    }
}
