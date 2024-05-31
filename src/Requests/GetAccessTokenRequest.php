<?php

declare(strict_types=1);

namespace Mvenghaus\SaloonPlentyConnector\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Traits\Plugins\AcceptsJson;

final class GetAccessTokenRequest extends Request implements HasBody
{
    use HasFormBody;
    use AcceptsJson;

    protected Method $method = Method::POST;

    public function __construct(
        protected OAuthConfig $oauthConfig
    ) {
    }

    public function resolveEndpoint(): string
    {
        return $this->oauthConfig->getTokenEndpoint();
    }

    public function defaultBody(): array
    {
        return [
            'username' => $this->oauthConfig->getClientId(),
            'password' => $this->oauthConfig->getClientSecret(),
        ];
    }
}
