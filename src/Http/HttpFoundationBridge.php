<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

use PhrameCMS\Core\Contracts\HttpTransportInterface;

final class HttpFoundationBridge implements HttpTransportInterface
{
    private const SYMFONY_REQUEST = 'Symfony\\Component\\HttpFoundation\\Request';
    private const SYMFONY_RESPONSE = 'Symfony\\Component\\HttpFoundation\\Response';

    public static function isAvailable(): bool
    {
        return class_exists(self::SYMFONY_REQUEST) && class_exists(self::SYMFONY_RESPONSE);
    }

    public static function requestFromGlobals(): Request
    {
        return (new self())->captureRequest();
    }

    public static function sendResponse(Response $response): void
    {
        (new self())->emitResponse($response);
    }

    public function captureRequest(): Request
    {
        $requestClass = self::SYMFONY_REQUEST;

        /** @var object $request */
        $request = $requestClass::createFromGlobals();

        return self::toCoreRequest($request);
    }

    public function emitResponse(Response $response): void
    {
        self::toSymfonyResponse($response)->send();
    }

    private static function toCoreRequest(object $request): Request
    {
        if (!method_exists($request, 'getContent') || !method_exists($request, 'getMethod') || !method_exists($request, 'getPathInfo')) {
            throw new \RuntimeException('Invalid Symfony Request instance.');
        }

        $body = $request->getContent();
        $jsonBody = null;

        if ($body !== '') {
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }

        /** @var array<string, mixed> $query */
        $query = $request->query->all();

        /** @var array<string, array<int, string>> $headers */
        $headers = $request->headers->all();

        return new Request(
            strtoupper($request->getMethod()),
            $request->getPathInfo(),
            $query,
            self::flattenHeaders($headers),
            $jsonBody,
        );
    }

    private static function toSymfonyResponse(Response $response): object
    {
        $responseClass = self::SYMFONY_RESPONSE;

        return new $responseClass(
            $response->body(),
            $response->status(),
            $response->headers(),
        );
    }

    /**
     * @param array<string, array<int, string>> $headers
     *
     * @return array<string, string>
     */
    private static function flattenHeaders(array $headers): array
    {
        $flat = [];

        foreach ($headers as $name => $values) {
            $flat[$name] = implode(', ', $values);
        }

        return $flat;
    }
}
