<?php

namespace App\Tests\Infrastructure\Api;

use App\Infrastructure\Api\AuthenticationResponse;
use App\Infrastructure\Api\FranceTravailAuthenticationApi;
use App\Infrastructure\Exception\ApiException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class FranceTravailAuthenticationApiTest extends TestCase
{

    public function testItReturnsAuthenticationResponse(): void {
        $logger       = $this->createMock(LoggerInterface::class);
        $clientId     = "x";
        $clientSecret = "y";

        $responseBody   = json_encode([
            "access_token" => "token",
            "token_type"   => "Bearer",
            "expires_in"   => 1499,
            "scope"        => "refresh_token",
        ]);
        $url        = 'https://francetravail.io/connexion/oauth2/access_token?realm=partenaire';
        $httpClient = new MockHttpClient(new MockResponse($responseBody), $url);

        $franceTravailAuthenticationApi = new FranceTravailAuthenticationApi(
            $httpClient,
            $logger,
            $clientId,
            $clientSecret,
        );

        $authenticationResponse = $franceTravailAuthenticationApi->authenticate();

        $this->assertInstanceOf(AuthenticationResponse::class, $authenticationResponse);
    }

    /**
     * If the Api's answer is missing the expires_in attribute, FranceTravailAuthenticationApi should set a default value
     */
    public function testItSetsDefaultTtlIfMissingFromResponse(): void {
        $logger       = $this->createMock(LoggerInterface::class);
        $clientId     = "x";
        $clientSecret = "y";

        $responseBody   = json_encode([
            "access_token" => "token",
            "token_type"   => "Bearer",
            "scope"        => "refresh_token",
        ]);
        $url        = 'https://francetravail.io/connexion/oauth2/access_token?realm=partenaire';
        $httpClient = new MockHttpClient(new MockResponse($responseBody), $url);

        $franceTravailAuthenticationApi = new FranceTravailAuthenticationApi(
            $httpClient,
            $logger,
            $clientId,
            $clientSecret,
        );

        $authenticationResponse = $franceTravailAuthenticationApi->authenticate();

        $this->assertEquals(3600, $authenticationResponse->ttl);
    }

    /**
     * If the Authentication API returns a code other than 200 FranceTravailAuthenticationApi should throw ApiException
     */
    public function testItThrowsApiExceptionIfAuthenticationFails(): void {
        $logger       = $this->createMock(LoggerInterface::class);
        $clientId     = "x";
        $clientSecret = "y";

        $response = new MockResponse(
            "",
            [
                'http_code' => 401,
            ]
        );
        $url        = 'https://francetravail.io/connexion/oauth2/access_token?realm=partenaire';
        $httpClient = new MockHttpClient($response, $url);

        $franceTravailAuthenticationApi = new FranceTravailAuthenticationApi(
            $httpClient,
            $logger,
            $clientId,
            $clientSecret,
        );

        $this->expectException(ApiException::class);

        $franceTravailAuthenticationApi->authenticate();
    }

    public function testItThrowsApiExceptionIfResponseIsMissingAccessToken(): void {
        $logger       = $this->createMock(LoggerInterface::class);
        $clientId     = "x";
        $clientSecret = "y";

        $response = new MockResponse(
            json_encode([
                "token_type" => "Bearer",
                "expires_in" => 1499,
                "scope"      => "refresh_token",
            ]),
        );
        $url        = 'https://francetravail.io/connexion/oauth2/access_token?realm=partenaire';
        $httpClient = new MockHttpClient($response, $url);

        $franceTravailAuthenticationApi = new FranceTravailAuthenticationApi(
            $httpClient,
            $logger,
            $clientId,
            $clientSecret,
        );

        $this->expectException(ApiException::class);

        $franceTravailAuthenticationApi->authenticate();
    }
}
