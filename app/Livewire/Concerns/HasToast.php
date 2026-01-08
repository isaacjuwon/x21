<?php

namespace App\Livewire\Concerns;

trait HasToast
{
    /**
     * Dispatch a success toast notification
     */
    public function toastSuccess(string $content, int $duration = 4000): void
    {
        $this->toast($content, 'success', $duration);
    }

    /**
     * Dispatch a warning toast notification
     */
    public function toastWarning(string $content, int $duration = 4000): void
    {
        $this->toast($content, 'warning', $duration);
    }

    /**
     * Dispatch an error toast notification
     */
    public function toastError(string $content, int $duration = 4000): void
    {
        $this->toast($content, 'error', $duration);
    }

    /**
     * Dispatch an info toast notification
     */
    public function toastInfo(string $content, int $duration = 4000): void
    {
        $this->toast($content, 'info', $duration);
    }

    /**
     * Dispatch a toast notification
     */
    public function toast(string $content, string $type = 'info', int $duration = 4000): void
    {
        $this->dispatch('notify',
            type: $type,
            content: $content,
            duration: $duration
        );
    }
}
