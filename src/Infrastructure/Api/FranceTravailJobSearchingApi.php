<?php

namespace App\Infrastructure\Api;

use App\Application\Service\TokenProviderInterface;
use App\Domain\Model\Job;
use App\Infrastructure\Exception\ApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FranceTravailJobSearchingApi implements ExternalJobSearchingApiInterface
{
    private const PROVIDER_NAME = "france-travail";

    private const RESULT_KEY    = "resultats";
    private const MAX_RESULT    = 100;

    private const API_URL       = "https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search";

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly TokenProviderInterface $tokenProvider,
        private readonly string $clientId,
    ) {

    }

    /**
     * @param FranceTravailJobSearchingParams $params
     * @return Job[]
     * @throws ApiException
     */
    public function fetchJobs(JobSearchingParamsInterface $params): array
    {
        try {
            $token = $this->tokenProvider->getToken(self::PROVIDER_NAME, $this->clientId);
        } catch (\Throwable $exception) {
            Throw new ApiException($exception->getMessage());
        }

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $range       = sprintf("%d-%d", $params->rangeStart, $params->rangeStart + self::MAX_RESULT);
        $queryParams = [
            'commune' => implode(",", $params->commune),
            'range'   => $range,
        ];

        try {
            $response = $this->httpClient->request(
                'GET',
                self::API_URL,
                [
                    'headers' => $headers,
                    'query'   => $queryParams,
                ]
            );
        } catch (\Throwable $exception) {
            $msg = sprintf("Error when fetching jobs: %s", $exception->getMessage());
            $this->logger->error($msg, ['exception' => $exception]);

            throw new ApiException($msg);
        }

        $responseCode = $response->getStatusCode();

        if(Response::HTTP_NO_CONTENT === $responseCode) {
            return [];
        }

        if(
            Response::HTTP_PARTIAL_CONTENT !== $responseCode &&
            Response::HTTP_OK !== $responseCode
        ) {
            $msg = sprintf(
                "Could not retrieve jobs from France Travail API: code %d",
                $responseCode
            );

            throw new ApiException($msg);
        }

        $content         = $response->getContent();
        $decodedResponse = json_decode($content, true);
        if(null === $decodedResponse) {
            throw new ApiException("Could not read jobs from France Travail API's response.");
        }

        if(!array_key_exists(self::RESULT_KEY, $decodedResponse)) {
            $this->logger->info("France Travail API's response was empty.");

            return [];
        }

        $jobs = [];
        foreach($decodedResponse[self::RESULT_KEY] as $job) {
            $jobs[] = $this->parseToJob($job);
        }

        return $jobs;
    }

    /**
     * TODO check if json key is not missing
     * TODO add textSanitizer
     */
    private function parseToJob(array $jsonJob): Job {
        $title = $jsonJob['intitule'];

        if(!array_key_exists('entreprise', $jsonJob) || !array_key_exists('nom', $jsonJob['entreprise'])) {
            $companyName = null;
        } else {
            $companyName = $jsonJob['entreprise']['nom'];
        }

        $location    = $jsonJob['lieuTravail']['libelle'];
        $description = $jsonJob['description'];
        $applyUrl    = $jsonJob['origineOffre']['urlOrigine'];
        $postedDate  = new \DateTime($jsonJob['dateCreation']);

        return new Job(
            $title,
            $companyName,
            $location,
            $description,
            $applyUrl,
            $postedDate,
        );
    }
}
