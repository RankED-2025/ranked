<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TopCoursesRequestDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $top = 5,
    ) {}
}
