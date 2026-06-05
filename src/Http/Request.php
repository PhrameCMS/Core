<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

final class Request
{
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
        $method = HttpMethod::fromString((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $rawPath = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) parse_url($rawPath, PHP_URL_PATH);
        $query = $_GET;
        $headers = self::readHeaders();
        $body = file_get_contents('php://input') ?: '';

        $decoded = null;
        if ($body !== '') {
            $json = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $decoded = $json;
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
            $headers = getallheaders();
            if (is_array($headers)) {
                /** @var array<string, string> $headers */
                return $headers;
            }
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $headerName = str_replace('_', '-', strtolower(substr($key, 5)));
            $headers[$headerName] = (string) $value;
        }

        return $headers;
    }
}
