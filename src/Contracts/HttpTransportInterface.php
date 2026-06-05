<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;

interface HttpTransportInterface
{
    public function captureRequest(): Request;

    public function emitResponse(Response $response): void;
}
