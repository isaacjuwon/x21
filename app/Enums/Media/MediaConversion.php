<?php

namespace App\Enums\Media;

enum MediaConversion: string
{
    case ORIGINAL = 'original';
    case SM = 'small';
    case MD = 'medium';
    case LG = 'large';
}
