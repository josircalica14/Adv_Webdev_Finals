<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $apiKey = config('services.groq.key');
        if (!$apiKey) {
            return response()->json(['reply' => 'AI assistant is not configured.'], 500);
        }

        $systemPrompt = $this->buildSystemPrompt($request);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->withoutVerifying()->timeout(20)
          ->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'       => 'llama-3.3-70b-versatile',
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $request->input('message')],
            ],
            'max_tokens'  => 400,
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            return response()->json(['reply' => 'Sorry, I could not get a response. Please try again.'], 500);
        }

        $reply = $response->json('choices.0.message.content') ?? 'No response received.';

        return response()->json(['reply' => trim($reply)]);
    }

    private function buildSystemPrompt(Request $request): string
    {
        $currentUser = $request->user();

        // --- Gather live platform data ---
        $users = User::where('is_admin', false)
            ->with(['portfolio.items' => function ($q) {
                $q->where('is_visible', true)->select('portfolio_id', 'item_type', 'title', 'description', 'tags', 'item_date');
            }])
            ->select('id', 'full_name', 'username', 'program', 'bio', 'contact_info')
            ->get();

        // Build a compact student directory
        $studentLines = [];
        foreach ($users as $user) {
            $items = $user->portfolio?->items ?? collect();

            // Collect all tags across items
            $allTags = $items->flatMap(fn($i) => (array)($i->tags ?? []))->unique()->values()->take(15);

            // Group item titles by type
            $byType = $items->groupBy('item_type')->map(fn($g) => $g->pluck('title')->implode(', '));

            $line = "- {$user->full_name} (@{$user->username}, {$user->program})";
            if ($user->bio) {
                $line .= ': ' . \Str::limit($user->bio, 120);
            }
            if ($allTags->isNotEmpty()) {
                $line .= ' | Skills/Tags: ' . $allTags->implode(', ');
            }
            foreach ($byType as $type => $titles) {
                $line .= " | {$type}s: " . \Str::limit($titles, 100);
            }
            // Social links
            $ci = $user->contact_info ?? [];
            $links = collect(['github', 'linkedin', 'website'])->filter(fn($k) => !empty($ci[$k]))->map(fn($k) => "$k: {$ci[$k]}")->implode(', ');
            if ($links) $line .= " | Links: $links";

            $studentLines[] = $line;
        }

        $studentDirectory = implode("\n", $studentLines);
        $totalStudents    = $users->count();
        $bsitCount        = $users->where('program', 'BSIT')->count();
        $cseCount         = $users->where('program', 'CSE')->count();

        $loggedInInfo = $currentUser
            ? "The user currently chatting is: {$currentUser->full_name} (@{$currentUser->username}, {$currentUser->program})."
            : "The user is browsing as a guest (not logged in).";

        return <<<PROMPT
You are a helpful AI assistant embedded in "Portfolio Platform" — a student portfolio showcase for BSIT and CSE students.

PLATFORM OVERVIEW:
- Students create portfolios with items: projects, achievements, milestones, skills, experience, education
- Public showcase at /showcase — browse and filter all student portfolios
- Each student has a public portfolio at /portfolio/{username}
- Dashboard features: Add Item, Customize (theme/fonts/colors), Profile (bio + social links), Export PDF
- Programs: BSIT (Bachelor of Science in Information Technology), CSE (Computer Science Engineering)

CURRENT USER:
{$loggedInInfo}

PLATFORM STATS:
- Total students: {$totalStudents} ({$bsitCount} BSIT, {$cseCount} CSE)

STUDENT DIRECTORY (live data):
{$studentDirectory}

YOUR ROLE:
- Help users find students by name, skill, program, or project type
- Answer questions about the platform features and how to use them
- If asked "who works with X" or "find students with Y skill", search the directory above and list matching students with their portfolio link (/portfolio/{username})
- Keep responses concise and friendly
- If you recommend a student, always include their portfolio link
- Do not make up data — only use what's in the directory above
PROMPT;
    }
}
