<?php

declare(strict_types=1);

namespace Mvenghaus\SaloonPlentyConnector;

use Closure;
use Mvenghaus\SaloonPlentyConnector\Requests\GetAccessTokenRequest;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class AuthConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;
    use ClientCredentialsGrant;

    public function __construct(
        public Configuration $configuration,
    ) {
        if ($this->configuration?->debugCallback instanceof Closure) {
            $this->debugRequest($this->configuration->debugCallback);
        }
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->status() !== 200;
    }

    public function updateAccessToken(): void
    {
        $authenticator = $this->getAccessToken();

        $this->authenticate(new TokenAuthenticator($authenticator->getAccessToken()));

        $this->configuration->authenticator = $authenticator->serialize();

        if ($this->configuration->authenticatorUpdateCallback instanceof Closure) {
            ($this->configuration->authenticatorUpdateCallback)($authenticator->serialize());
        }
    }

    public function resolveBaseUrl(): string
    {
        return $this->configuration->endpoint;
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId($this->configuration->username)
            ->setClientSecret($this->configuration->password)
            ->setTokenEndpoint('login');
    }

    protected function resolveAccessTokenRequest(
        OAuthConfig $oauthConfig,
        array $scopes = [],
        string $scopeSeparator = ' '
    ): Request {
        return new GetAccessTokenRequest($oauthConfig);
    }
}
