<?php

declare(strict_types=1);

namespace PhrameCMS\Wysiwyg;

use PhrameCMS\Core\Contracts\RouteProviderInterface;
use PhrameCMS\Core\Http\Response;
use PhrameCMS\Core\Routing\Route;

final class WysiwygRouteProvider implements RouteProviderInterface
{
    public function routes(): array
    {
        return [
            Route::create('GET', '/plugins/wysiwyg/status', static fn (): Response => Response::json([
                'plugin' => 'wysiwyg',
                'enabled' => true,
                'mode' => 'placeholder',
            ])),
        ];
    }
}
