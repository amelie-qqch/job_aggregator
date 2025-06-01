<?php

namespace App\Application\Service;

interface TokenProviderInterface
{
    public function getToken(string $provider, string $clientId): string;

}
