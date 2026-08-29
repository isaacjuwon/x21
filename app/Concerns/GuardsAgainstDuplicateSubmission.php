<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;
use WendellAdriel\Idempotency\Idempotency;

/**
 * Provides the same lock-then-check-then-store idempotency shape that the
 * Idempotent middleware uses at the HTTP layer, applied manually inside
 * Livewire components whose write actions route through Livewire's shared
 * internal update endpoint (POST /livewire/update) rather than a named route.
 *
 * Integration pattern — in the component class:
 *
 *   use GuardsAgainstDuplicateSubmission;
 *
 *   public string $formToken = '';
 *
 * In the Blade form, use the package @idempotency directive bound to the
 * component property so Livewire syncs the value back on submit:
 *
 *   <form wire:submit="submit">
 *
 *       @idempotency($formToken ?: null)
 *       <input type="hidden" name="_idempotency_key" wire:model="formToken" />
 *       ...
 *   </form>
 *
 * In the action method:
 *
 *   public function submit(SomeAction $action): void
 *   {
 *       if (! $this->acquireSubmissionLock()) {
 *           return; // duplicate in flight
 *       }
 *       try {
 *           if ($this->submissionAlreadyCompleted()) {
 *               $this->redirect(route('somewhere'), navigate: true);
 *               return;
 *           }
 *           $action->handle(…);
 *           $this->markSubmissionComplete();
 *       } finally {
 *           $this->releaseSubmissionLock();
 *       }
 *       $this->rotateFormToken(); // fresh key for the next distinct operation
 *   }
 */
trait GuardsAgainstDuplicateSubmission
{
    /**
     * Try to acquire the in-flight atomic lock.
     *
     * Lazily initialises $formToken via Idempotency::key() if the property is
     * still empty (e.g. the form rendered without the @idempotency directive
     * filling it in yet).
     *
     * Returns false when a duplicate submission is already in progress.
     *
     * @param  int  $lockSeconds  How long (seconds) to hold the lock while processing.
     */
    protected function acquireSubmissionLock(int $lockSeconds = 30): bool
    {
        if (empty($this->formToken)) {
            $this->formToken = Idempotency::key();
        }

        $lock = Cache::lock($this->lockCacheKey(), $lockSeconds);

        if (! $lock->get()) {
            return false;
        }

        $this->_submissionLockOwner = $lock->owner();

        return true;
    }

    /**
     * Release the in-flight lock acquired by acquireSubmissionLock().
     */
    protected function releaseSubmissionLock(): void
    {
        if (isset($this->_submissionLockOwner)) {
            Cache::restoreLock($this->lockCacheKey(), $this->_submissionLockOwner)->release();
            unset($this->_submissionLockOwner);
        }
    }

    /**
     * Check whether the current formToken was already completed successfully.
     */
    protected function submissionAlreadyCompleted(): bool
    {
        return Cache::has($this->doneCacheKey());
    }

    /**
     * Mark the current formToken as successfully completed.
     *
     * @param  int  $ttlSeconds  How long (seconds) to remember the completed state.
     */
    protected function markSubmissionComplete(int $ttlSeconds = 86400): void
    {
        Cache::put($this->doneCacheKey(), true, now()->addSeconds($ttlSeconds));
    }

    /**
     * Rotate $formToken so the next distinct operation gets a fresh key.
     * Call this after a successful submission, outside the try/finally block.
     */
    protected function rotateFormToken(): void
    {
        $this->formToken = Idempotency::key();
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /** @var string|null Transient lock-owner handle (not persisted to Livewire state). */
    private ?string $_submissionLockOwner = null;

    private function lockCacheKey(): string
    {
        return 'livewire-idempotency:'.$this->formToken;
    }

    private function doneCacheKey(): string
    {
        return 'livewire-idempotency-done:'.$this->formToken;
    }
}
