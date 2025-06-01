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
            ->addOption(
                'report',
                null,
                InputOption::VALUE_OPTIONAL,
                '[Not implemented yet] Whether to display a report of the jobs retrieved,
                if the option is passed without value the report will be displayed in the console,
                otherwise please specify a path for the report file.',
            )
            ->addUsage(
                "app:fetch-jobs --location=35238 --location=75113 --location=33063 --report=./report.csv",
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupérer les city code
        // TODO Ajouter un paramètre report
        $query = new JobSearchingQuery(
            self::DEFAULT_CITY_CODES,
        );

        try {
            $this->handler->handle($query);
        } catch (DomainException $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        } catch (\Throwable $exception) {
            $msg = sprintf("Error when fetching jobs: %s", $exception->getMessage());
            $output->writeln($msg);
        }

        return COMMAND::FAILURE;
    }


}
