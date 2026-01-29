<?php

use App\Models\Page;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function delete(Page $page)
    {
        $page->delete();
        $this->dispatch('page-deleted');
    }

    #[Computed]
    public function pages()
    {
        return Page::query()->latest()->paginate(10);
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <x-ui.heading variant="h1" class="text-xl font-bold text-neutral-900 dark:text-white">Pages</x-ui.heading>
        <x-ui.button :href="route('admin.pages.create')" variant="primary" icon="plus">Create Page</x-ui.button>
    </div>

    <x-ui.table class="text-xs">
        <x-slot:header>
            <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Title</x-ui.table.header>
            <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Slug</x-ui.table.header>
            <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Status</x-ui.table.header>
            <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Last Updated</x-ui.table.header>
            <x-ui.table.header></x-ui.table.header>
        </x-slot:header>

        <x-slot:body>
            @foreach ($this->pages as $page)
                <x-ui.table.row wire:key="{{ $page->id }}" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                    <x-ui.table.cell class="font-bold text-neutral-900 dark:text-white">{{ $page->title }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-neutral-500">{{ $page->slug }}</x-ui.table.cell>
                    <x-ui.table.cell shadow="none">
                        <x-ui.badge :color="$page->is_published ? 'success' : 'neutral'" class="text-[10px]">
                            {{ $page->is_published ? 'Published' : 'Draft' }}
                        </x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-neutral-400">{{ $page->updated_at->diffForHumans() }}</x-ui.table.cell>
                    <x-ui.table.cell>
                        <div class="flex justify-end gap-2">
                            <x-ui.button :href="route('admin.pages.edit', $page)" size="sm" variant="ghost" icon="pencil" />
                            <x-ui.button wire:click="delete({{ $page->id }})" wire:confirm="Are you sure you want to delete this page?" size="sm" variant="ghost" color="danger" icon="trash" />
                        </div>
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
        </x-slot:body>
    </x-ui.table>

    <div class="mt-4">
        {{ $this->pages->links() }}
    </div>
</div>
