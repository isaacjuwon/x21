<?php

use App\Models\Page;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

new class extends Component {
    public Page $page;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public bool $is_published = false;

    public function mount(Page $page)
    {
        $this->page = $page;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->content = $page->content ?? '';
        $this->is_published = $page->is_published;
    }

    public function updatedTitle($value)
    {
        // Only update slug if it hasn't been manually changed? 
        // For simplicity, let's not auto-update slug on edit to avoid breaking links unintentionally
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $this->page->id,
            'content' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $this->page->update($validated);

        return redirect()->route('admin.pages.index');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <x-ui.heading>Edit Page</x-ui.heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="space-y-6">
                <x-ui.field>
                    <x-ui.label for="title">Title</x-ui.label>
                    <x-ui.input wire:model="title" id="title" />
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

                <div class="flex items-center gap-2">
                    <x-ui.checkbox wire:model="is_published" id="is_published" />
                    <x-ui.label for="is_published" class="mb-0">Publish Page</x-ui.label>
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.pages.index')" variant="ghost">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">Update Page</x-ui.button>
        </div>
    </form>
</div>
