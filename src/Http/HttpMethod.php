<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Http;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';

    public static function fromString(string $method): self
    {
        return self::tryFrom(strtoupper($method)) ?? self::GET;
    }
}
