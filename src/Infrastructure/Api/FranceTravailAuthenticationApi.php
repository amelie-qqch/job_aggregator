<?php

namespace App\Infrastructure\Api;

use App\Infrastructure\Exception\ApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FranceTravailAuthenticationApi implements AuthenticationApiInterface
{
    private const PROVIDER_NAME     = "france-travail";
    private const DEFAULT_TOKEN_TTL = 3600;
    private const API_URL           = "https://francetravail.io/connexion/oauth2/access_token?realm=partenaire";
    private const GRANT_TYPE        = "client_credentials";
    private const SCOPE             = "api_offresdemploiv2 o2dsoffre";

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $clientId,
        private readonly string $clientSecret,
    )
    {
    }

    /**
     * @return AuthenticationResponse
     *
     * @throws ApiException
     */
    public function authenticate(): AuthenticationResponse
    {
        $body = [
            "grant_type"    => self::GRANT_TYPE,
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "scope"         => self::SCOPE,
        ];
        $headers = [
            "Content-type: application/x-www-form-urlencoded",
        ];

        try {
            $response = $this->client->request(
                'POST',
                self::API_URL,
                [
                    'headers' => $headers,
                    'body'    => $body,
                ]
            );
        } catch(\Throwable $exception) {
            $msg = sprintf("Error when fetching jobs: %s", $exception->getMessage());
            $this->logger->error($msg, ['exception' => $exception]);

            throw new ApiException($msg);
        }

        $responseCode = $response->getStatusCode();
        if(Response::HTTP_OK !== $responseCode) {
            $msg = sprintf(
                "Authentication to France Travail API failed: code %d",
                $responseCode,
            );

            throw new ApiException($msg);
        }

        $content         = $response->getContent();
        $decodedResponse = json_decode($content, true);
        if(null === $decodedResponse || !array_key_exists('access_token', $decodedResponse)) {
            throw new ApiException("Could not read authentication token from response.");
        }

        $expiresIn = array_key_exists('expires_in', $decodedResponse) ? $decodedResponse['expires_in'] : self::DEFAULT_TOKEN_TTL;

        return new AuthenticationResponse(
            $decodedResponse["access_token"],
            $expiresIn,
        );
    }

    public static function getIndex(): string {
        return self::PROVIDER_NAME;
    }
}
