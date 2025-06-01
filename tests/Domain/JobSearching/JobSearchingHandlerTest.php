<?php

namespace App\Tests\Domain\JobSearching;

use App\Domain\JobSearching\JobSearchingHandler;
use App\Domain\JobSearching\JobSearchingQuery;
use App\Domain\Model\Job;
use App\Domain\Repository\JobRepositoryInterface;
use App\Infrastructure\Api\ExternalJobSearchingApiInterface;
use PHPUnit\Framework\TestCase;

class JobSearchingHandlerTest extends TestCase
{

    public function testReturnsArrayOrThrows(): void {
        $externalJobApi = $this->createMock(ExternalJobSearchingApiInterface::class);
        $externalJobApi
            ->expects($this->once())
            ->method("fetchJobs")
            ->willReturn([
                0 => [
                    "id" => "193DNBS",
                ]
            ]);

        $jobRepository = $this->createMock(JobRepositoryInterface::class);
//        $jobRepository
//            ->expects($this->once())
//            ->method("batchCreate")
//            ->will

        $handler = new JobSearchingHandler(
            $externalJobApi,
            $jobRepository
        );

        $jobs = $handler->handle(new JobSearchingQuery());

        $this->assertCount(1, $jobs);
        $job = array_shift($jobs);
        $this->assertInstanceOf(Job::class, $job);

        $this->assertEquals("Assistant de direction/Assistant personnel (H/F)", $job->getTitle());
        $this->assertEquals("VM AGENCY", $job->getCompanyName());
        $this->assertEquals("75 - Paris 5e Arrondissement", $job->getLocation());
        $this->assertEquals("Vous êtes organisé(e), autonome, et capable d'épauler une direction sans problème ? Vous avez le goût du travail bien fait, l'esprit pratique, et un sens du service affûté ? Ce poste pourrait être le vôtre.\n\nNous recherchons un(e) assistant(e) de direction/assistant(e) personnel(le) pour accompagner un dirigeant dans la gestion de ses activités quotidiennes et professionnelles.\n\nVos missions :\nGestion d'agenda, coordination de réunions et prise de rendez-vous\nOrganisation de déplacements professionnels (billets, hôtels, logistique)\nSuivi administratif courant : gestion des mails, appels, dossiers, classement\nRédaction et mise en forme de documents, compte-rendus\nLien avec le personnel de maison le cas échéant\nDéplacements ponctuels à prévoir en zone nationale\n\nProfil recherché :\nAutonome, structuré(e) et doté(e )d'un excellent sens de la confidentialité\nA l'aise avec les outils numériques et bureautiques\nPrésentation soignée et excellente communication orale et écrite\nCapacité à gérer les priorités dans un environnement exigeant", $job->getDescription());
    }

}
