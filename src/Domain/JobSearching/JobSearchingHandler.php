<?php

namespace App\Domain\JobSearching;

use App\Domain\Exception\CouldNotCreateEntityException;
use App\Domain\Model\Job;
use App\Domain\Repository\JobRepositoryInterface;
use App\Infrastructure\Api\ExternalJobSearchingApiInterface;
use Psr\Log\LoggerInterface;

class JobSearchingHandler
{
    public function __construct(
        private readonly ExternalJobSearchingApiInterface $jobApi,
        private readonly JobRepositoryInterface           $jobRepository,
        private readonly LoggerInterface                  $logger
    ) {
    }

    /**
     * @param JobSearchingQuery $query
     * @return Job[]
     * @throws CouldNotCreateEntityException
     */
    public function handle(JobSearchingQuery $query): array {
        $params = [
            'location' => $query->locations,
            'from'     => $query->from,
        ];

        $jobs = $this->jobApi->fetchJobs($params);

        try {
            $this->jobRepository->batchCreate($jobs);
        } catch (\Throwable $exception) {
            $this->logger->error(
                sprintf("Error when saving jobs to database: %s", $exception->getMessage()),
                ['exception' => $exception]
            );

            throw new CouldNotCreateEntityException();
        }


        return $jobs;
    }

}
