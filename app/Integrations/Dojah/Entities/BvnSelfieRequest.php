<?php
  
declare(strict_types=1);
  
namespace App\Integrations\Dojah\Entities;
  
final readonly class BvnSelfieRequest
{
    public function __construct(
        public string $bvn,
        public string $selfieImage, // Base64 encoded image
    ) {}
  
    public function toRequestBody(): array
    {
        return [
            'bvn' => $this->bvn,
            'selfie_image' => $this->selfieImage,
        ];
    }
}
