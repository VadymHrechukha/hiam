<?php
declare(strict_types=1);

namespace hiam\providers;

interface ReturnUrlsProviderInterface
{
    public function getReturnUrls(): array;
}
