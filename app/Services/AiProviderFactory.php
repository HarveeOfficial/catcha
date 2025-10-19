<?php

namespace App\Services;

class AiProviderFactory
{
    public static function make(string $provider): AiProvider
    {
        return match ($provider) {
            'gemini' => new GeminiProvider,
            'openai' => new OpenAiProvider,
            default => new OpenAiProvider, // default
        };
    }

    public static function getAvailableProviders(): array
    {
        $providers = [];
        if (config('services.openai.key')) {
            $providers['openai'] = 'OpenAI (GPT-4o)';
        }
        if (config('services.gemini.key')) {
            $providers['gemini'] = 'Gemini (Google AI)';
        }

        return $providers;
    }
}
