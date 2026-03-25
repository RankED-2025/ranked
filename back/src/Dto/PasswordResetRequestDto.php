<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PasswordResetRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email = '',
    ) {}
}
