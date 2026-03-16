<?php

namespace App\Models\Concerns;

use App\Enums\Media\MediaConversion;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;

trait HasMediaConvertionRegistrations
{
    use InteractsWithMedia;

    public function modelMediaConvertionRegistrations(): callable
    {
        return function () {
            $this->addMediaConversion(MediaConversion::Original->value)->nonOptimized()->nonQueued();
            $this->addMediaConversion(MediaConversion::Sm->value)->fit(Fit::Crop, 300, 300)->nonQueued();
            $this->addMediaConversion(MediaConversion::Md->value)->fit(Fit::Crop, 500, 500)->nonQueued();
            $this->addMediaConversion(MediaConversion::Lg->value)->fit(Fit::Crop, 800, 800)->nonQueued();
        };
    }
}
