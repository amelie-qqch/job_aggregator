<?php

namespace App\Tests\Domain\JobSearching;

use App\Domain\Exception\DomainException;
use App\Domain\JobSearching\JobSearchingHandler;
use App\Domain\JobSearching\JobSearchingQuery;
use App\Domain\Model\Job;
use App\Domain\Repository\JobRepositoryInterface;
use App\Infrastructure\Api\ExternalJobSearchingApiInterface;
use App\Infrastructure\Exception\ApiException;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JobSearchingHandlerTest extends TestCase
{

    public function testItReturnsAnArrayOfJobs(): void {
        $externalJobApi = $this->createMock(ExternalJobSearchingApiInterface::class);
        $externalJobApi
            ->expects($this->once())
            ->method("fetchJobs")
            ->willReturn([
                new Job(
                    "title",
                    "company",
                    "location",
                    "description",
                    "applyUrl",
                    new \DateTime(),
                )
            ]);

        $logger        = $this->createMock(LoggerInterface::class);
        $jobRepository = $this->createMock(JobRepositoryInterface::class);
        $jobRepository
            ->expects($this->once())
            ->method("batchCreate")
        ;

        $handler = new JobSearchingHandler($externalJobApi, $jobRepository, $logger);

        $jobs = $handler->handle(new JobSearchingQuery());

        $this->assertCount(1, $jobs);
        $job = array_shift($jobs);
        $this->assertInstanceOf(Job::class, $job);
    }

    public function testItThrowsApiExceptionIfJobApiFails(): void {
        $externalJobApi = $this->createMock(ExternalJobSearchingApiInterface::class);
        $externalJobApi
            ->expects($this->once())
            ->method("fetchJobs")
            ->willThrowException(new ApiException());

        $logger        = $this->createMock(LoggerInterface::class);
        $jobRepository = $this->createMock(JobRepositoryInterface::class);

        $handler = new JobSearchingHandler($externalJobApi, $jobRepository, $logger);
        $this->expectException(ApiException::class);

        $handler->handle(new JobSearchingQuery());
    }

    public function testItThrowsDomainExceptionIfRepositoryFails(): void {
        $externalJobApi = $this->createMock(ExternalJobSearchingApiInterface::class);
        $externalJobApi
            ->expects($this->once())
            ->method("fetchJobs")
        ;

        $logger        = $this->createMock(LoggerInterface::class);
        $jobRepository = $this->createMock(JobRepositoryInterface::class);
        $jobRepository
            ->expects($this->once())
            ->method("batchCreate")
            ->willThrowException(new Exception());
        ;

        $handler = new JobSearchingHandler($externalJobApi, $jobRepository, $logger);
        $this->expectException(DomainException::class);

        $handler->handle(new JobSearchingQuery());
    }

}
