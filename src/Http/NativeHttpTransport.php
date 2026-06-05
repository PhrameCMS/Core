<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

use PhrameCMS\Core\Contracts\HttpTransportInterface;

final class NativeHttpTransport implements HttpTransportInterface
{
    public function captureRequest(): Request
    {
        return Request::fromGlobals();
    }

    public function emitResponse(Response $response): void
    {
        $response->send();
    }
}
