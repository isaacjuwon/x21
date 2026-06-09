<?php

class TestHooks
{
    public string $bvn {
        set(string $value) => trim($value);
    }

    public ?string $firstName {
        set(?string $value) => $value ? trim($value) : null;
    }

    public function __construct(string $bvn, ?string $firstName = null)
    {
        $this->bvn = $bvn;
        $this->firstName = $firstName;
    }
}
