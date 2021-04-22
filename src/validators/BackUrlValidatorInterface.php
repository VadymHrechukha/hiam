<?php
declare(strict_types=1);

namespace hiam\validators;

interface BackUrlValidatorInterface
{
    public function validate(string $url): bool;
}
