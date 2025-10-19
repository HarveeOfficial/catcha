<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProvider
{
    protected string $apiKey;

    protected string $model;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
        $this->timeout = (int) config('services.openai.timeout', 30);
    }

    /**
     * Send a message to OpenAI and get a response.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{answer: string, model: string, usage: ?array}
     */
    public function chat(array $messages): array
    {
        $fallbackChain = [
            $this->model,
            'gpt-4o-mini',
            'gpt-3.5-turbo',
        ];

        foreach ($fallbackChain as $model) {
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 400,
            ];

            $resp = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($resp->successful()) {
                $json = $resp->json();
                $answer = $json['choices'][0]['message']['content'] ?? null;
                if ($answer) {
                    $answer = preg_replace('/\*\*(.*?)\*\*/s', '$1', $answer); // Remove bold
                    $answer = preg_replace('/^#{1,6}\s*/m', '', $answer); // Remove headings
                }

                return [
                    'answer' => $answer,
                    'model' => $model,
                    'usage' => $json['usage'] ?? null,
                ];
            }

            $error = $resp->json();
            $code = $error['error']['code'] ?? $error['error']['type'] ?? null;
            // Non-model errors (auth, rate limit) -> stop immediately
            if (! in_array($code, ['model_not_found', 'invalid_model', 'not_found'])) {
                break;
            }
        }

        throw new \Exception('OpenAI request failed after trying: '.implode(', ', $fallbackChain));
    }
}
