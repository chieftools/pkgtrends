<?php

namespace ChiefTools\Pkgtrends\Jobs\Concerns;

trait LogsMessages
{
    protected function logMessage(string $message): void
    {
        logger()->info('[' . static::class . '] ' . $message);
    }
}
