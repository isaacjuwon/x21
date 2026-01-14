<?php

namespace App\Enums\Kyc;

enum Status: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'blue',
            self::Verified => 'green',
            self::Failed => 'red',
        };
    }

    public function getLabel(): null|string
    {
        return $this->value;
    }

    public static function match(string|null $value): Status
    {
        return match ($value) {
            Status::Pending->value => Status::Pending,
            Status::Verified->value => Status::Verified,
            Status::Failed->value => Status::Failed,
            default => Status::Pending,
        };
    }

    public static function toSelect($placeholder = false): array
    {
        $array = [];
        $index = 0;
        if ($placeholder) {
            $array[$index++] = [
                'label' => $placeholder === true ? 'Select status' : $placeholder,
                'value' => '',
            ];
        }
        foreach (self::cases() as $case) {
            $array[$index++] = [
                'label' => $case->getLabel(),
                'value' => $case->value,
            ];
        }
        return $array;
    }
}
