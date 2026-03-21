<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiScoreService
{
    private string $apiKey;
    private string $model = 'llama-3.3-70b-versatile';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
    }

    public function getFeedback(array $portfolioData): array
    {
        $prompt = $this->buildPrompt($portfolioData);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => $this->model,
                'max_tokens'  => 400,
                'temperature' => 0.7,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a career advisor reviewing student portfolios. Be concise, constructive, and encouraging. Respond ONLY with valid JSON, no markdown.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return ['error' => 'Connection failed: ' . $e->getMessage()];
        }

        if ($response->failed()) {
            $body = $response->json();
            $msg  = $body['error']['message'] ?? $response->body();
            return ['error' => '(' . $response->status() . ') ' . $msg];
        }

        $content = $response->json('choices.0.message.content', '');

        $content = preg_replace('/^```json\s*/i', '', trim($content));
        $content = preg_replace('/```$/', '', trim($content));

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'AI returned an unexpected response.'];
        }

        return $data;
    }

    private function buildPrompt(array $d): string
    {
        $items = collect($d['items'])->map(fn($i) =>
            "- [{$i['type']}] {$i['title']}: {$i['description']}"
        )->join("\n");

        return <<<PROMPT
Analyze this student portfolio and return a JSON object with exactly these keys:
{
  "overall": "2-3 sentence overall impression",
  "strengths": ["strength 1", "strength 2"],
  "improvements": ["improvement 1", "improvement 2"],
  "tip": "one actionable quick tip"
}

Student: {$d['name']}
Program: {$d['program']}
Bio: {$d['bio']}
Skills: {$d['skills']}
Portfolio items ({$d['item_count']}):
{$items}
PROMPT;
    }
}
