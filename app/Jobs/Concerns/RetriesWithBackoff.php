<?php

namespace ChiefTools\Pkgtrends\Jobs\Concerns;

trait RetriesWithBackoff
{
    public function tries(): int
    {
        return 10;
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 180, 300, 600];
    }
}
