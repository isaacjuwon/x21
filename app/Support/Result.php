<?php

namespace App\Support;

use Throwable;

final readonly class Result
{
    public function __construct(
        public mixed $value,
        public null|Throwable $error = null,
    ) {}

    public static function ok(mixed $value): Result
    {
        return new self(
            value: $value,
            error: null,
        );
    }

    public static function error(Throwable $error): Result
    {
        return new self(
            value: null,
            error: $error,
        );
    }

    public function isOk(): bool
    {
        return $this->error === null;
    }

    public function isError(): bool
    {
        return $this->error !== null;
    }

    /**
     * @return mixed
     * @throws Throwable
     */
    public function unwrap(): mixed
    {
        if ($this->isError()) {
            throw $this->error;
        }

        return $this->value;
    }

}
