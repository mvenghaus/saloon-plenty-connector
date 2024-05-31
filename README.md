# Saloon - Plentymarkets Connector

[Saloon](https://docs.saloon.dev/) - Plentymarkets Connector with token handling, allowing you to easily start building your own requests.

## Installation

Install the package via composer:

```bash
composer require mvenghaus/saloon-plenty-connector
```

## Usage

### Basic Structure

```php

$configuration = new Configuration(...);
$connector = new Connector($configuration);

$response = $connector->send(new Your_Request());
```

### Configuration - Structure

```php
class Configuration
{
    public function __construct(
        public string $endpoint, // https://www.your-domain.com/rest/
        public string $username,
        public string $password,
        public ?string $authenticator = null, // saloon authenticator (serialized)
        public ?Closure $authenticatorUpdateCallback = null, // callback to save authenticator if changed
        public ?Closure $debugCallback = null // callback for debugging
    ) {
    }
}
```

### Configuration - Example

```php
$authenticator = load_from_your_cache();

$configuration = new Configuration(
    'https://www.your-domain.com/rest/',
    'USERNAME',
    'PASSWORD',
    $authenticator,
    function (string $authenticator) {
        save_to_your_cache($authenticator);
    },
    function (PendingRequest $pendingRequest, RequestInterface $psrRequest) {
        echo $pendingRequest->getUrl() . PHP_EOL;
    }
);
```
