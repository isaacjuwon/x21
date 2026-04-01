<?php

namespace App\Services;

use App\Models\Faq;
use App\Settings\IntegrationSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class AiSupportService
{
    public function __construct(private readonly IntegrationSettings $settings) {}

    public function ask(string $question): string
    {
        $faqs = $this->findRelevantFaqs($question);

        $context = $faqs->isNotEmpty()
            ? $faqs->map(fn ($faq) => "Q: {$faq->question}\nA: {$faq->answer}")->implode("\n\n")
            : 'No specific FAQ entries found.';

        $apiKey = $this->settings->openai_api_key;

        if (! $apiKey) {
            return $this->fallbackResponse($faqs, $question);
        }

        $model = $this->settings->openai_model ?: 'gpt-4o-mini';

        $response = Http::withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a helpful customer support assistant. Use the FAQ knowledge base below to answer user questions accurately and concisely. If the answer isn't in the FAQs, provide a helpful general response and suggest the user open a support ticket.\n\nFAQ Knowledge Base:\n{$context}",
                    ],
                    [
                        'role' => 'user',
                        'content' => $question,
                    ],
                ],
                'max_tokens' => 500,
                'temperature' => 0.3,
            ]);

        if ($response->failed()) {
            return $this->fallbackResponse($faqs, $question);
        }

        return $response->json('choices.0.message.content', 'Sorry, I could not process your request. Please try again or open a support ticket.');
    }

    private function findRelevantFaqs(string $question): Collection
    {
        $words = collect(explode(' ', strtolower($question)))
            ->filter(fn ($w) => strlen($w) > 3)
            ->unique()
            ->take(5);

        if ($words->isEmpty()) {
            return Faq::active()->take(5)->get();
        }

        return Faq::active()
            ->where(function ($query) use ($words) {
                foreach ($words as $word) {
                    $query->orWhere('question', 'like', "%{$word}%")
                        ->orWhere('answer', 'like', "%{$word}%");
                }
            })
            ->take(5)
            ->get();
    }

    private function fallbackResponse(Collection $faqs, string $question): string
    {
        if ($faqs->isNotEmpty()) {
            $faq = $faqs->first();

            return "Based on our FAQ: {$faq->answer}\n\nIf this doesn't answer your question, please open a support ticket and our team will assist you.";
        }

        return "I couldn't find a specific answer to your question. Please open a support ticket and our team will get back to you shortly.";
    }
}
