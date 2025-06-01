<?php

namespace App\Infrastructure\Api;

interface AuthenticationApiInterface
{
    public function authenticate(): AuthenticationResponse;
}
