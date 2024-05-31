<?php

declare(strict_types=1);

namespace Mvenghaus\SaloonPlentyConnector;

use Closure;
use Mvenghaus\SaloonPlentyConnector\Requests\GetAccessTokenRequest;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class Connector extends \Saloon\Http\Connector
{
    use AlwaysThrowOnErrors;
    use AcceptsJson;
    use ClientCredentialsGrant;

    public function __construct(
        public Configuration $configuration,
    ) {
        if ($this->configuration->debugCallback instanceof Closure) {
            $this->debugRequest($this->configuration->debugCallback);
        }

        if (empty($this->configuration->authenticator)) {
            $this->updateAccessToken();
            return;
        }

        $authenticator = AccessTokenAuthenticator::unserialize($this->configuration->authenticator);

        $this->authenticate(new TokenAuthenticator($authenticator->getAccessToken()));

        if ($authenticator->hasExpired()) {
            $this->updateAccessToken();
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
