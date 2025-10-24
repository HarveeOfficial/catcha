<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProvider
{
    protected string $apiKey;

    protected string $model;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
        $this->timeout = (int) config('services.gemini.timeout', 30);
    }

    /**
     * Send a message to Gemini and get a response.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{answer: string, model: string, usage: ?array}
     */
    public function chat(array $messages): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured');
        }

        // Convert messages to Gemini format
        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $msg['content']],
                ],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $client = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ]);

            // For development environments with certificate issues
            if (app()->environment('local')) {
                $client = $client->withoutVerifying();
            }

            $resp = $client->post($url, $payload);

            if ($resp->successful()) {
                $json = $resp->json();

                // Check for errors in response
                if (isset($json['error'])) {
                    Log::error('Gemini API error response', $json['error']);
                    throw new \Exception('Gemini error: '.($json['error']['message'] ?? 'Unknown error'));
                }

                // Verify response structure
                if (! isset($json['candidates']) || ! is_array($json['candidates']) || empty($json['candidates'])) {
                    Log::error('Gemini response missing candidates', ['response' => $json]);
                    throw new \Exception('Gemini returned no candidates');
                }

                $candidate = $json['candidates'][0];

                // Safely extract text from nested structure
                $answer = null;
                if (isset($candidate['content']) && is_array($candidate['content'])) {
                    if (isset($candidate['content']['parts']) && is_array($candidate['content']['parts'])) {
                        if (! empty($candidate['content']['parts']) && isset($candidate['content']['parts'][0]['text'])) {
                            $answer = $candidate['content']['parts'][0]['text'];
                        }
                    }
                }

                if (! $answer) {
                    $finishReason = $candidate['finishReason'] ?? 'UNKNOWN';
                    Log::error('Gemini response missing text in candidate', [
                        'finish_reason' => $finishReason,
                        'candidate_keys' => array_keys($candidate),
                        'content_keys' => isset($candidate['content']) ? array_keys($candidate['content']) : 'missing',
                        'candidate_dump' => $candidate,
                    ]);

                    // Handle MAX_TOKENS specially - it means the response was cut off
                    if ($finishReason === 'MAX_TOKENS') {
                        throw new \Exception('Gemini response was truncated (MAX_TOKENS). Increase maxOutputTokens.');
                    }

                    throw new \Exception('Gemini returned unexpected candidate structure');
                }

                // Clean up markdown
                $answer = preg_replace('/\*\*(.*?)\*\*/s', '$1', $answer); // Remove bold
                $answer = preg_replace('/^#{1,6}\s*/m', '', $answer); // Remove headings

                Log::debug('Gemini response successful', [
                    'model' => $this->model,
                    'answer_length' => strlen($answer),
                ]);

                return [
                    'answer' => $answer,
                    'model' => $this->model,
                    'usage' => $json['usageMetadata'] ?? null,
                ];
            } else {
                $status = $resp->status();
                $body = $resp->body();
                $json = $resp->json();

                Log::error('Gemini API request failed', [
                    'status' => $status,
                    'body' => $body,
                    'json' => $json,
                    'url' => substr($url, 0, 100),
                ]);

                $errorMsg = $json['error']['message'] ?? $body;
                throw new \Exception("Gemini request failed (HTTP {$status}): {$errorMsg}");
            }
        } catch (\Exception $e) {
            Log::error('Gemini provider exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}
