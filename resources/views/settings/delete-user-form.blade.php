<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $password = "";

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            "password" => ["required", "string", "current_password"],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect("/", navigate: true);
    }
};
?>

<section class="mt-10 space-y-6">
    <div class="relative mb-8">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ __('Delete Account') }}</h2>
        <p class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mt-1">{{ __('Permanently remove your account and all associated data') }}</p>
    </div>

    <x-ui.modal.trigger id="confirm-user-deletion">
        <x-ui.button variant="danger" x-data="" class="h-12 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-error/20" data-test="delete-user-button">
            {{ __('Delete account') }}
        </x-ui.button>
    </x-ui.modal.trigger>

    <x-ui.modal id="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ __('Are you sure you want to delete your account?') }}</h3>

                <p class="mt-2 text-xs font-bold text-neutral-500 dark:text-neutral-400 leading-relaxed tracking-wide">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </div>

            <x-ui.field>
                <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Password') }}</x-ui.label>
                <x-ui.input wire:model="password" type="password" class="text-base font-bold tracking-widest h-14" />
                <x-ui.error name="password" />
            </x-ui.field>

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button type="button" x-on:click="$dispatch('close-modal', 'confirm-user-deletion')" variant="outline" class="h-12 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">
                    {{ __('Cancel') }}
                </x-ui.button>

                <x-ui.button variant="danger" type="submit" class="h-12 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-error/20" data-test="confirm-delete-user-button">
                    {{ __('Delete account') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</section>
