<?php

use Mvenghaus\SaloonPlentyConnector\ApiConnector;
use Mvenghaus\SaloonPlentyConnector\AuthConnector;
use Mvenghaus\SaloonPlentyConnector\Configuration;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('retrieves access token if no authenticator is provided', function () {
    $configuration = new Configuration('dummy', 'dummy', 'dummy');

    $mockClient = new MockClient([
        '*' => MockResponse::make(json_encode([
            'access_token' => 'NEW_ACCESS_TOKEN',
        ]))
    ]);

    $authConnectorMock = Mockery::mock(AuthConnector::class, [$configuration])
        ->makePartial()
        ->withMockClient($mockClient);

    $authConnectorMock->shouldReceive('updateAccessToken')->passthru()->once();

    $apiConnector = Mockery::mock(ApiConnector::class, [$configuration])->makePartial();
    $apiConnector->shouldReceive('getAuthConnector')->andReturn($authConnectorMock);

    $authenticator = $apiConnector->resolveAuthenticator();

    expect($authenticator->getAccessToken())->toBe('NEW_ACCESS_TOKEN');
});

it('retrieves new access token if expired', function () {
    $oldAuthenticator = new AccessTokenAuthenticator(
        accessToken:'OLD_ACCESS_TOKEN',
        expiresAt: new DateTimeImmutable('2000-01-01')
    );

    $configuration = new Configuration('dummy', 'dummy', 'dummy', $oldAuthenticator->serialize());

    $mockClient = new MockClient([
        '*' => MockResponse::make(json_encode([
            'access_token' => 'NEW_ACCESS_TOKEN',
        ]))
    ]);

    $authConnectorMock = Mockery::mock(AuthConnector::class, [$configuration])
        ->makePartial()
        ->withMockClient($mockClient);

    $authConnectorMock->shouldReceive('updateAccessToken')->passthru()->once();

    $apiConnector = Mockery::mock(ApiConnector::class, [$configuration])->makePartial();
    $apiConnector->shouldReceive('getAuthConnector')->andReturn($authConnectorMock);

    $authenticator = $apiConnector->resolveAuthenticator();

    expect($authenticator->getAccessToken())->toBe('NEW_ACCESS_TOKEN');
});

it('does not refresh token if not expired', function () {
    $oldAuthenticator = new AccessTokenAuthenticator(
        accessToken:'OLD_ACCESS_TOKEN',
        expiresAt: (new DateTimeImmutable())->add(new DateInterval('P1D'))
    );

    $configuration = new Configuration('dummy', 'dummy', 'dummy', $oldAuthenticator->serialize());

    $mockClient = new MockClient([
        '*' => MockResponse::make(json_encode([
            'access_token' => 'NEW_ACCESS_TOKEN',
        ]))
    ]);

    $authConnectorMock = Mockery::mock(AuthConnector::class, [$configuration])
        ->makePartial()
        ->withMockClient($mockClient);

    $authConnectorMock->shouldReceive('updateAccessToken')->passthru()->never();

    $apiConnector = Mockery::mock(ApiConnector::class, [$configuration])->makePartial();
    $apiConnector->shouldReceive('getAuthConnector')->andReturn($authConnectorMock);

    $authenticator = $apiConnector->resolveAuthenticator();

    expect($authenticator->getAccessToken())->toBe('OLD_ACCESS_TOKEN');
});