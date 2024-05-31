<?php

declare(strict_types=1);

namespace Mvenghaus\SaloonPlentyConnector;

use Closure;

class Configuration
{
    public function __construct(
        public string $endpoint,
        public string $username,
        public string $password,
        public ?string $authenticator = null,
        public ?Closure $authenticatorUpdateCallback = null,
        public ?Closure $debugCallback = null
    ) {
    }
}