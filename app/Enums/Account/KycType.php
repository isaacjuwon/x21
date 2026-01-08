<?php

namespace App\Enums\Account;

enum KycType: string
{
    case BVN = 'BVN';
    case NIN = 'NIN';

    public static function toSelect($placeholder = false): array
    {
        $array = [];
        $index = 0;
        if ($placeholder) {
            $array[$index]['id'] = '';
            $array[$index]['name'] = '-- Select --';
            $index++;
        }

        foreach (self::cases() as $case) {
            $array[$index]['id'] = $case->value;
            $array[$index]['name'] = $case->value;
            $index++;
        }

        return $array;
    }

}
