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

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <x-ui.heading>Pages</x-ui.heading>
        <x-ui.button :href="route('admin.pages.create')" variant="primary">Create Page</x-ui.button>
    </div>

    <x-ui.table>
        <x-slot:header>
            <x-ui.table.header>Title</x-ui.table.header>
            <x-ui.table.header>Slug</x-ui.table.header>
            <x-ui.table.header>Status</x-ui.table.header>
            <x-ui.table.header>Last Updated</x-ui.table.header>
            <x-ui.table.header></x-ui.table.header>
        </x-slot:header>

        <x-slot:body>
            @foreach ($this->pages as $page)
                <x-ui.table.row wire:key="{{ $page->id }}">
                    <x-ui.table.cell>{{ $page->title }}</x-ui.table.cell>
                    <x-ui.table.cell>{{ $page->slug }}</x-ui.table.cell>
                    <x-ui.table.cell>
                        <x-ui.badge :color="$page->is_published ? 'success' : 'neutral'">
                            {{ $page->is_published ? 'Published' : 'Draft' }}
                        </x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell>{{ $page->updated_at->diffForHumans() }}</x-ui.table.cell>
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
