<?php

use App\Services\AiSupportService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('AI Support')] class extends Component {
    public string $message = '';

    public array $conversation = [];

    public bool $isThinking = false;

    public function sendMessage(AiSupportService $aiSupport): void
    {
        $this->validate(['message' => ['required', 'string', 'min:3', 'max:500']]);

        $userMessage = trim($this->message);
        $this->message = '';

        $this->conversation[] = [
            'role' => 'user',
            'content' => $userMessage,
            'time' => now()->format('H:i'),
        ];

        $answer = $aiSupport->ask($userMessage);

        $this->conversation[] = [
            'role' => 'assistant',
            'content' => $answer,
            'time' => now()->format('H:i'),
        ];
    }

    public function clearConversation(): void
    {
        $this->conversation = [];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('AI Support Assistant') }}</flux:heading>
            <flux:subheading>{{ __('Ask anything — powered by our FAQ knowledge base.') }}</flux:subheading>
        </div>
        @if(count($this->conversation) > 0)
            <flux:button wire:click="clearConversation" variant="ghost" icon="trash" size="sm">
                {{ __('Clear') }}
            </flux:button>
        @endif
    </div>

    {{-- Chat window --}}
    <flux:card class="flex flex-col gap-4 p-4 min-h-96">
        @if(empty($this->conversation))
            <div class="flex flex-col items-center justify-center flex-1 py-16 text-center gap-3">
                <flux:icon name="chat-bubble-left-right" class="size-12 text-zinc-300 dark:text-zinc-600" />
                <flux:heading>{{ __('How can I help you?') }}</flux:heading>
                <flux:text class="text-zinc-400 max-w-sm">
                    {{ __('Ask a question about our services, account, or anything else. I\'ll do my best to help.') }}
                </flux:text>
            </div>
        @else
            <div class="flex flex-col gap-4" x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'end' })">
                @foreach($this->conversation as $entry)
                    @if($entry['role'] === 'user')
                        <div class="flex justify-end">
                            <div class="max-w-[75%] space-y-1">
                                <div class="bg-violet-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm">
                                    {{ $entry['content'] }}
                                </div>
                                <flux:text class="text-xs text-zinc-400 text-end">{{ $entry['time'] }}</flux:text>
                            </div>
                        </div>
                    @else
                        <div class="flex justify-start gap-2">
                            <div class="size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0 mt-0.5">
                                <flux:icon name="sparkles" class="size-4 text-violet-500" />
                            </div>
                            <div class="max-w-[75%] space-y-1">
                                <div class="bg-zinc-100 dark:bg-zinc-800 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm whitespace-pre-wrap">
                                    {{ $entry['content'] }}
                                </div>
                                <flux:text class="text-xs text-zinc-400">{{ $entry['time'] }}</flux:text>
                            </div>
                        </div>
                    @endif
                @endforeach

                <div wire:loading wire:target="sendMessage" class="flex justify-start gap-2">
                    <div class="size-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                        <flux:icon name="sparkles" class="size-4 text-violet-500" />
                    </div>
                    <div class="bg-zinc-100 dark:bg-zinc-800 rounded-2xl rounded-tl-sm px-4 py-2.5">
                        <div class="flex gap-1 items-center h-5">
                            <span class="size-2 bg-zinc-400 rounded-full animate-bounce [animation-delay:0ms]"></span>
                            <span class="size-2 bg-zinc-400 rounded-full animate-bounce [animation-delay:150ms]"></span>
                            <span class="size-2 bg-zinc-400 rounded-full animate-bounce [animation-delay:300ms]"></span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </flux:card>

    {{-- Input --}}
    <form wire:submit="sendMessage" class="flex gap-2">
        <div class="flex-1">
            <flux:input
                wire:model="message"
                placeholder="{{ __('Type your question...') }}"
                autofocus
                :disabled="$isThinking"
            />
            @error('message')
                <flux:text class="text-red-500 text-xs mt-1">{{ $message }}</flux:text>
            @enderror
        </div>
        <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled" wire:target="sendMessage">
            {{ __('Send') }}
        </flux:button>
    </form>

    <flux:callout icon="information-circle" color="blue" variant="secondary">
        <flux:callout.text>
            {{ __('Can\'t find what you need?') }}
            <flux:link :href="route('tickets.create')" wire:navigate>{{ __('Open a support ticket') }}</flux:link>
            {{ __('and our team will assist you.') }}
        </flux:callout.text>
    </flux:callout>
</div>
