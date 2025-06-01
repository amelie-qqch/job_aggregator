<?php

namespace App\Domain\JobSearching;

class JobSearchingQuery
{
    public function __construct(
        public readonly array $locations = [],
        public readonly int $from        = 0
    ) {

    }
}
