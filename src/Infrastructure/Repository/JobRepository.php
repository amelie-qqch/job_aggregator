<?php

namespace App\Infrastructure\Repository;

use App\Domain\Model\Job;
use App\Domain\Repository\JobRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

class JobRepository extends ServiceEntityRepository implements JobRepositoryInterface
{
    private const BATCH_SIZE = 100;
    private const TABLE_NAME = 'job';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    public function create(Job $job): void
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param Job[] $jobs
     * @return void
     * @throws Exception
     */
    public function batchCreate(array $jobs): void {
        $connection = $this->getEntityManager()->getConnection();

        $baseSql = sprintf("INSERT INTO %s (title, location, company_name, description, apply_url, date_posted) VALUES ", self::TABLE_NAME);

        $insert = [];
        $index  = 0;
        $params = [];
        foreach ($jobs as $job) {
            $insert[] = sprintf(
                '(:title_%1$d, :location_%1$d, :companyName_%1$d, :description_%1$d, :applyUrl_%1$d, :datePosted_%1$d)',
                $index
            );
            $params = array_merge($params,[
                "title_$index"       => $job->getTitle(),
                "location_$index"    => $job->getLocation(),
                "companyName_$index" => $job->getCompanyName(),
                "description_$index" => $job->getDescription(),
                "applyUrl_$index"    => $job->getApplyUrl(),
                "datePosted_$index"  => $job->getDatePosted()->format('Y-m-d H:i:s'),
            ]);

            $index++;

            if($index === self::BATCH_SIZE) {
                $sql = $baseSql . implode(",", $insert);
                $connection->executeStatement($sql, $params);
                $params = [];
                $insert = [];
                $index  = 0;
            }
        }

        if([] !== $params) {
            $sql = $baseSql . implode(",", $insert);
            $connection->executeStatement($sql, $params);
        }

    }
}
