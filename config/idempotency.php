<?php

declare(strict_types=1);

use WendellAdriel\Idempotency\Enums\IdempotencyScope;

return [
    /*
    |--------------------------------------------------------------------------
    | Idempotency TTL
    |--------------------------------------------------------------------------
    |
    | Number of seconds an idempotency key should remain valid (default 1 hour).
    |
    */
    'ttl' => env('IDEMPOTENCY_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Idempotency Required Header
    |--------------------------------------------------------------------------
    |
    | Stricter than the old homegrown middleware — clients MUST supply the key.
    |
    */
    'required' => env('IDEMPOTENCY_REQUIRED', true),

    /*
    |--------------------------------------------------------------------------
    | Idempotency Scope
    |--------------------------------------------------------------------------
    |
    | Supported values: user, ip, global. "user" falls back to IP when no
    | authenticated user is available.
    |
    */
    'scope' => env('IDEMPOTENCY_SCOPE', IdempotencyScope::User->value),

    /*
    |--------------------------------------------------------------------------
    | Idempotency Header
    |--------------------------------------------------------------------------
    */
    'header' => env('IDEMPOTENCY_HEADER', 'Idempotency-Key'),

    /*
    |--------------------------------------------------------------------------
    | Idempotency Request Input
    |--------------------------------------------------------------------------
    |
    | Fallback input field name checked when the header is absent (used by
    | MapWebhookIdempotencyKey and the Livewire hidden-field pattern).
    |
    */
    'input' => env('IDEMPOTENCY_INPUT', '_idempotency_key'),

    /*
    |--------------------------------------------------------------------------
    | Idempotency Lock Timeout
    |--------------------------------------------------------------------------
    */
    'lock_timeout' => env('IDEMPOTENCY_LOCK_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Idempotency Cache Statuses
    |--------------------------------------------------------------------------
    |
    | client_error and server_error are disabled so that clients can retry
    | after validation failures rather than getting stuck on a burned key.
    |
    */
    'cache_statuses' => [
        'informational' => true,
        'success' => true,
        'redirection' => true,
        'client_error' => false,
        'server_error' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Index Locks
    |--------------------------------------------------------------------------
    */
    'strict_index_locks' => env('IDEMPOTENCY_STRICT_INDEX_LOCKS', false),
];
