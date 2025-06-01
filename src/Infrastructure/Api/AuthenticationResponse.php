<?php

namespace App\Infrastructure\Api;

class AuthenticationResponse
{
    public function __construct(
        public readonly string $token,
        public readonly int $ttl
    ) {

    }
}
