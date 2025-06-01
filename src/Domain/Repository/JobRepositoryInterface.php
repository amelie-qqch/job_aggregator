<?php

namespace App\Domain\Repository;

use App\Domain\Model\Job;

interface JobRepositoryInterface
{
    public function create(Job $job): void;

    /**
     * @param Job[] $jobs
     * @return void
     */
    public function batchCreate(array $jobs): void;

}
