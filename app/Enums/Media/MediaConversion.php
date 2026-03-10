<?php

namespace App\Enums\Media;

enum MediaConversion: string
{
    case Original = 'original';
    case Sm = 'small';
    case Md = 'medium';
    case Lg = 'large';
}
