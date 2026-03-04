<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PasswordResetConfirmDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $token = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $password = '',
    ) {}
}
