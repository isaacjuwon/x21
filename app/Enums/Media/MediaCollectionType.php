<?php

namespace App\Enums\Media;

enum MediaCollectionType: string
{
    case UserProfile = 'user-profile';
    case Avatar = 'avatars';
    case Brand = 'brands';
    case FilamentMessages = 'filament-messages';
}
