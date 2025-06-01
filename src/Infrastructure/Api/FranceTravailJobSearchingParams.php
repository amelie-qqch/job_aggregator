<?php

namespace App\Infrastructure\Api;

class FranceTravailJobSearchingParams implements JobSearchingParamsInterface
{

    public function __construct(
        public readonly array $commune,
        public readonly int $rangeStart
    )
    {
    }

}
