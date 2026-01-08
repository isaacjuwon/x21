<?php

namespace App\Enums\Media;

enum MediaCollectionType: string
{
    case USER_PROFILE = 'user-profile';
    case Avatar = 'avatars';
    case Brand = 'brands';
    case FILAMENT_MESSAGES = 'filament-messages';
}
