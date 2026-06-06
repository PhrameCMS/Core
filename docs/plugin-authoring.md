# Plugin Authoring Guide

## Goals

Plugins should be installable, removable, and upgradable without changing core source code.

## Package Requirements

- Publish as a Composer package.
- Require PHP 8.2+.
- Require `phramecms/core`.
- Provide PSR-4 autoloading.

## Composer Metadata

Use `extra.phramecms` in your plugin `composer.json`.

```json
{
  "extra": {
    "phramecms": {
      "provider": "Vendor\\Package\\MyServiceProvider",
      "capabilities": ["feature.example"]
    }
  }
}
```

`provider` can be replaced by `providers` if multiple service providers are needed.

## Service Provider Contract

Implement `PhrameCMS\\Core\\Contracts\\ServiceProviderInterface`.

- `register`: define services and tags.
- `boot`: finalize runtime behavior after registration.

## Route Contribution

1. Implement `PhrameCMS\\Core\\Contracts\\RouteProviderInterface`.
2. Register the route provider service in `register`.
3. Tag the service with `ServiceTag::RouteProvider`.

Example:

```php
use PhrameCMS\Core\Contracts\ServiceTag;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\Response;
use PhrameCMS\Core\Routing\Route;

$container->tag(ServiceTag::RouteProvider, ExampleRouteProvider::class);

Route::create(HttpMethod::GET, '/plugins/example/status', static fn (): Response => Response::json([
  'ok' => true,
]));
```

## Controller Auto-Discovery (Manifest)

In addition to tagged `RouteProviderInterface` services, packages can declare controller routes directly in
`extra.phramecms.controllers`.

```json
{
  "extra": {
    "phramecms": {
      "provider": "Vendor\\Package\\MyServiceProvider",
      "controllers": [
        {
          "method": "GET",
          "path": "/plugins/example/page",
          "controller": "Vendor\\Package\\Controller\\PageController"
        },
        {
          "method": "POST",
          "path": "/plugins/example/save",
          "controller": "Vendor\\Package\\Controller\\PageController::save"
        }
      ]
    }
  }
}
```

Supported controller references:

- Invokable controller class (`__invoke`)
- `ClassName::methodName`

Controller actions must return `PhrameCMS\\Core\\Http\\Response`.

## Twig Rendering (Optional)

When `phramecms/twig-bridge` is installed, Core registers `PhrameCMS\\Core\\Contracts\\TemplateRendererInterface`
automatically.

```php
use PhrameCMS\Core\Contracts\TemplateRendererInterface;
use PhrameCMS\Core\Http\Response;

$container->set(MyPageController::class, static function ($container): MyPageController {
  /** @var TemplateRendererInterface $renderer */
  $renderer = $container->get(TemplateRendererInterface::class);

  return new MyPageController($renderer);
});

// In controller action:
return Response::html($this->renderer->render('plugin/page.html.twig', ['title' => 'Example']));
```

## Capability Reporting

Declare capability strings in `extra.phramecms.capabilities`.

These strings are exposed by `GET /capabilities` so clients can detect available features dynamically.

## Database Access (Optional)

When `phramecms/doctrine-dbal-bridge` is installed and enabled, Core registers
`PhrameCMS\Core\Contracts\DatabaseAdapterInterface` automatically.

```php
use PhrameCMS\Core\Contracts\DatabaseAdapterInterface;

$container->set(MyRepository::class, static function ($container): MyRepository {
  /** @var DatabaseAdapterInterface $db */
  $db = $container->get(DatabaseAdapterInterface::class);

  return new MyRepository($db);
});
```

If database support is optional in your plugin, check `has(DatabaseAdapterInterface::class)` before resolving it.

Example repository helper:

```php
use PhrameCMS\Core\Contracts\DatabaseAdapterInterface;

final class MyRepository
{
  public function __construct(private readonly DatabaseAdapterInterface $db)
  {
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  public function listPublishedPages(): array
  {
    return $this->db->fetchAll(
      'SELECT id, slug, title FROM pages WHERE published = :published ORDER BY id DESC',
      ['published' => 1],
    );
  }

  public function renamePage(int $id, string $title): bool
  {
    $affected = $this->db->execute(
      'UPDATE pages SET title = ? WHERE id = ?',
      [$title, $id],
    );

    return $affected > 0;
  }
}
```

## Compatibility Guidance

- Treat provider class names and behavior as part of your public plugin API.
- Follow semantic versioning.
- Keep cross-plugin coupling low by targeting core contracts only.
