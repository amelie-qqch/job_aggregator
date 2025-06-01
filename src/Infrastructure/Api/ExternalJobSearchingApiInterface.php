<?php

namespace App\Infrastructure\Api;

use App\Domain\Model\Job;

interface ExternalJobSearchingApiInterface
{

    /**
     * Fetches job listings based on the provided query.
     *
     * @return Job[]
     */
    public function fetchJobs(JobSearchingParamsInterface $params): array;
}
