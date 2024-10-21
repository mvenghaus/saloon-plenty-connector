<?php

declare(strict_types=1);

namespace Mvenghaus\SaloonPlentyConnector\Contracts;

use Mvenghaus\SaloonPlentyConnector\Connector;

interface ConnectorFactoryInterface {

    public function create(): Connector;

}