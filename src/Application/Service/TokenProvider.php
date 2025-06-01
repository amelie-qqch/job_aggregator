<?php

namespace App\Application\Service;

use App\Infrastructure\Api\AuthenticationApiInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TokenProvider implements TokenProviderInterface
{
    /**
     * @param <string,AuthenticationApiInterface>[] $authenticationApis
     */
    private array $authenticationApis;

    public function __construct(
        iterable $authenticationApis,
        private readonly CacheInterface $cache,
    )
    {
        $this->authenticationApis = iterator_to_array($authenticationApis);
    }

    public function getToken(string $provider, string $clientId): string
    {
        if(!array_key_exists($provider, $this->authenticationApis)) {
            throw new \Exception("Unsupported provider $provider");
        }

        $cacheKey = sprintf('%s-%s', $provider, $clientId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($provider) {
            /** @var AuthenticationApiInterface $authenticationApi */
            $authenticationApi = $this->authenticationApis[$provider];

            $payload = $authenticationApi->authenticate();
            $item->expiresAfter($payload->ttl);

            return $payload->token;
        });
    }
}
