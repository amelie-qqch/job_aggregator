<?php

namespace App\Command;

use App\Domain\Exception\DomainException;
use App\Domain\JobSearching\JobSearchingHandler;
use App\Domain\JobSearching\JobSearchingQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// TODO Ajouter un paramètre report
#[AsCommand('app:fetch-jobs')]
class FetchJobsCommand extends Command
{
    /**
     * Villes par défault :
     *  35238 : Rennes
     *  75113 : Paris
     *  33063 : Bordeaux
     */
    private const DEFAULT_CITY_CODES = [35238, 75113, 33063];
    public function __construct(
        private readonly JobSearchingHandler $handler,
    )
    {
        parent::__construct('app:fetch-jobs');
    }

    protected function configure(): void
    {
        $this
            ->setDescription("Fetch jobs from France Travail's API")
            ->addOption(
                'location',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Locations of the jobs, use the city code (INSEE code)',
                self::DEFAULT_CITY_CODES
            )
            ->addUsage(
                "app:fetch-jobs --location=35238 --location=75113 --location=33063",
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locations = $input->getOption('location');
        $query     = new JobSearchingQuery($locations);

        try {
            $this->handler->handle($query);
        } catch (DomainException $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        } catch (\Throwable $exception) {
            $msg = sprintf("Error when fetching jobs: %s", $exception->getMessage());
            $output->writeln($msg);

            return Command::FAILURE;
        }

        return COMMAND::SUCCESS;
    }


}
