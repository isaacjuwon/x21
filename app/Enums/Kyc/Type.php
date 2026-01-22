<?php

namespace App\Enums\Kyc;

enum Type: string
{
    case Bvn = 'bvn';
    case Nin = 'nin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Bvn => 'BVN',
            self::Nin => 'NIN',
        };
    }

    public static function toSelect($placeholder = false): array
    {
        $array = [];
        $index = 0;
        if ($placeholder) {
            $array[$index++] = [
                'label' => $placeholder === true ? 'Select Type' : $placeholder,
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
