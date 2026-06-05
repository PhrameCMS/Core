<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

final class Request
{
    /**
        * @param array<mixed, mixed> $query
     * @param array<string, string> $headers
        * @param array<mixed, mixed>|null $jsonBody
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $headers,
        public readonly ?array $jsonBody,
    ) {
    }

    public static function fromGlobals(): self
    {
        $rawMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $method = HttpMethod::fromString(is_string($rawMethod) ? $rawMethod : 'GET');

        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $rawPath = is_string($rawUri) ? $rawUri : '/';
        $parsedPath = parse_url($rawPath, PHP_URL_PATH);
        $path = is_string($parsedPath) ? $parsedPath : '/';

        /** @var array<mixed, mixed> $query */
        $query = $_GET;
        $headers = self::readHeaders();
        $body = file_get_contents('php://input') ?: '';

        /** @var array<mixed, mixed>|null $decoded */
        $decoded = null;
        if ($body !== '') {
            $decodedRaw = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = is_array($decodedRaw) ? $decodedRaw : null;
            }
        }

        return new self($method, $path, $query, $headers, $decoded);
    }

    /**
     * @return array<string, string>
     */
    private static function readHeaders(): array
    {
        if (function_exists('getallheaders')) {
            /** @var array<string, mixed> $headers */
            $headers = getallheaders();

            return self::normalizeHeaders($headers);
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            if (!is_scalar($value)) {
                continue;
            }

            $headerName = str_replace('_', '-', strtolower(substr($key, 5)));
            $headers[$headerName] = (string) $value;
        }

        return $headers;
    }

    /**
     * @param array<string, mixed> $headers
     *
     * @return array<string, string>
     */
    private static function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $normalized[$name] = (string) $value;
        }

        return $normalized;
    }
}
