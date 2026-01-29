<?php

use App\Models\Page;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    public string $title = '';

    public string $slug = '';

    public string $content = '';

    public bool $is_published = false;

    public function updatedTitle($value)
    {
        $this->slug = Str::slug($value);
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        Page::create($validated);

        return redirect()->route('admin.pages.index');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <x-ui.heading>Create Page</x-ui.heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="space-y-6">
                <x-ui.field>
                    <x-ui.label for="title">Title</x-ui.label>
                    <x-ui.input wire:model.live="title" id="title" />
                    <x-ui.error name="title" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="slug">Slug</x-ui.label>
                    <x-ui.input wire:model="slug" id="slug" />
                    <x-ui.error name="slug" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="content">Content (Markdown)</x-ui.label>
                    <x-ui.textarea wire:model="content" id="content" rows="15" class="font-mono" />
                    <x-ui.error name="content" />
                </x-ui.field>

                <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-2">
                        <x-ui.checkbox wire:model="is_published" id="is_published" />
                        <x-ui.label for="is_published" class="mb-0">Publish Page</x-ui.label>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.pages.index')" variant="ghost">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">Create Page</x-ui.button>
        </div>
    </form>
</div>
