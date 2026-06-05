<?php

declare(strict_types=1);

namespace Lume\Plugin\WysiwygPlaceholder;

use Lume\Core\Contracts\RouteProviderInterface;
use Lume\Core\Http\Response;
use Lume\Core\Routing\Route;

final class WysiwygRouteProvider implements RouteProviderInterface
{
    public function routes(): array
    {
        return [
            Route::create('GET', '/plugins/wysiwyg/status', static fn (): Response => Response::json([
                'plugin' => 'wysiwyg-placeholder',
                'enabled' => true,
                'mode' => 'placeholder',
            ])),
        ];
    }
}
