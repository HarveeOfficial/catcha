<?php

namespace App\Services;

interface AiProvider
{
    /**
     * Send a message to the AI and get a response.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{answer: string, model: string, usage: ?array}
     *
     * @throws \Exception
     */
    public function chat(array $messages): array;
}
