<?php

declare(strict_types=1);

namespace App\Attributes;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use WendellAdriel\Idempotency\Idempotency;

/**
 * Place on a public string property in any Livewire component to have it
 * automatically seeded with a stable idempotency key on first mount.
 *
 * Usage:
 *
 *   #[IdempotencyToken]
 *   public string $formToken = '';
 *
 * The key is seeded during mount() so the very first render already has a
 * value, preventing the mismatch between the Livewire snapshot and the
 *
 * @idempotency directive in the Blade template.
 *
 * On subsequent requests (hydrate) the key already lives in the snapshot, so
 * it is left untouched — guaranteeing the same key for the entire form
 * session until the component explicitly rotates it.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class IdempotencyToken extends LivewireAttribute
{
    /**
     * Seed the token when the component is first mounted (initial page load).
     */
    public function mount(): void
    {
        if (empty($this->getValue())) {
            $this->setValue(Idempotency::key());
        }
    }

    /**
     * Guard against an empty token surviving a hydration cycle
     * (e.g. the component was rendered server-side without a snapshot).
     */
    public function hydrate(): void
    {
        if (empty($this->getValue())) {
            $this->setValue(Idempotency::key());
        }
    }
}
