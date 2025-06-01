<?php

namespace App\Tests\Infrastructure\Api;

use App\Application\Service\TokenProviderInterface;
use App\Domain\Model\Job;
use App\Infrastructure\Api\FranceTravailJobSearchingApi;
use App\Infrastructure\Api\FranceTravailJobSearchingParams;
use App\Infrastructure\Exception\ApiException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class FranceTravailJobSearchingApiTest extends TestCase
{

    public function testItReturnsArrayOfJob(): void {
        $logger        = $this->createMock(LoggerInterface::class);
        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $clientId      = "x";

        $responseBody   = json_encode([
            "resultats" => [
                0 => [
                    "id" => "id",
                    "intitule" => "intitule",
                    "description" => "description",
                    "dateCreation" => "2025-05-30T11:47:36.339Z",
                    "lieuTravail" => [
                        "libelle" => "lieuTravail",
                    ],
                    "entreprise" => [
                        "nom" => "entreprise",
                    ],
                    "origineOffre" => [
                        "origine" => "1",
                        "urlOrigine" => "https://candidat.francetravail.fr/offres/recherche/detail/193DVQD"
                    ],
                ]
            ]
        ]);
        $response = new MockResponse(
            $responseBody,
            [
                'http_code' => Response::HTTP_OK,
            ]
        );
        $url        = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search';
        $httpClient = new MockHttpClient($response, $url);

        $jobSearchingApi = new FranceTravailJobSearchingApi(
            $httpClient,
            $logger,
            $tokenProvider,
            $clientId
        );

        $params = new FranceTravailJobSearchingParams(
            [35238],
            0
        );

        $jobs = $jobSearchingApi->fetchJobs($params);
        $this->assertIsArray($jobs);

        $job = array_pop($jobs);
        $this->assertInstanceOf(Job::class, $job);
    }

    public function testItReturnsEmptyArrayIfApiReturnsNoResult(): void {
        $logger        = $this->createMock(LoggerInterface::class);
        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $clientId      = "x";

        $response = new MockResponse(
            "",
            [
                'http_code' => Response::HTTP_NO_CONTENT,
            ]
        );
        $url        = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search';
        $httpClient = new MockHttpClient($response, $url);

        $jobSearchingApi = new FranceTravailJobSearchingApi(
            $httpClient,
            $logger,
            $tokenProvider,
            $clientId
        );

        $params = new FranceTravailJobSearchingParams(
            [35238],
            0
        );

        $jobs = $jobSearchingApi->fetchJobs($params);
        $this->assertIsArray($jobs);

        $this->assertCount(0, $jobs);
    }

    public function testItReturnsEmptyArrayIfResponseIsMissingResult(): void {
        $logger        = $this->createMock(LoggerInterface::class);
        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $clientId      = "x";

        $response = new MockResponse(
            "{}",
            [
                'http_code' => Response::HTTP_OK,
            ]
        );
        $url        = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search';
        $httpClient = new MockHttpClient($response, $url);

        $jobSearchingApi = new FranceTravailJobSearchingApi(
            $httpClient,
            $logger,
            $tokenProvider,
            $clientId
        );

        $params = new FranceTravailJobSearchingParams(
            [35238],
            0
        );

        $jobs = $jobSearchingApi->fetchJobs($params);
        $this->assertIsArray($jobs);

        $this->assertCount(0, $jobs);
    }

    public function testItSetsCompanyNameToUnknownIfMissing(): void {
        $logger        = $this->createMock(LoggerInterface::class);
        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $clientId      = "x";

        $responseBody   = json_encode([
            "resultats" => [
                0 => [
                    "id" => "id",
                    "intitule" => "intitule",
                    "description" => "description",
                    "dateCreation" => "2025-05-30T11:47:36.339Z",
                    "lieuTravail" => [
                        "libelle" => "lieuTravail",
                    ],
                    "origineOffre" => [
                        "origine" => "1",
                        "urlOrigine" => "https://candidat.francetravail.fr/offres/recherche/detail/193DVQD"
                    ],
                ]
            ]
        ]);
        $response = new MockResponse(
            $responseBody,
            [
                'http_code' => Response::HTTP_OK,
            ]
        );
        $url        = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search';
        $httpClient = new MockHttpClient($response, $url);

        $jobSearchingApi = new FranceTravailJobSearchingApi(
            $httpClient,
            $logger,
            $tokenProvider,
            $clientId
        );

        $params = new FranceTravailJobSearchingParams(
            [35238],
            0
        );

        $jobs = $jobSearchingApi->fetchJobs($params);
        $job = array_pop($jobs);

        $this->assertEquals("unknown", $job->getCompanyName());
    }

    public function testItThrowsApiExceptionIfSearchFailed(): void {
        $logger        = $this->createMock(LoggerInterface::class);
        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $clientId      = "x";

        $response = new MockResponse(
            "",
            [
                'http_code' => Response::HTTP_BAD_REQUEST,
            ]
        );
        $url        = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search';
        $httpClient = new MockHttpClient($response, $url);

        $jobSearchingApi = new FranceTravailJobSearchingApi(
            $httpClient,
            $logger,
            $tokenProvider,
            $clientId
        );

        $params = new FranceTravailJobSearchingParams(
            [35238],
            0
        );

        $this->expectException(ApiException::class);

        $jobSearchingApi->fetchJobs($params);
    }

    public function testItThrowsApiExceptionIfResponseContainsBadJson(): void {
        $logger        = $this->createMock(LoggerInterface::class);
        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $clientId      = "x";

        $response = new MockResponse(
            "{",
            [
                'http_code' => Response::HTTP_OK,
            ]
        );
        $url        = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search';
        $httpClient = new MockHttpClient($response, $url);

        $jobSearchingApi = new FranceTravailJobSearchingApi(
            $httpClient,
            $logger,
            $tokenProvider,
            $clientId
        );

        $params = new FranceTravailJobSearchingParams(
            [35238],
            0
        );

        $this->expectException(ApiException::class);

        $jobSearchingApi->fetchJobs($params);
    }


}
