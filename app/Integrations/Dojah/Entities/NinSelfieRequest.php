<?php
  
declare(strict_types=1);
  
namespace App\Integrations\Dojah\Entities;
  
final readonly class NinSelfieRequest
{
    public function __construct(
        public string $nin,
        public string $selfieImage, // Base64 encoded image
    ) {}
  
    public function toRequestBody(): array
    {
        return [
            'nin' => $this->nin,
            'selfie_image' => $this->selfieImage,
        ];
    }
}
