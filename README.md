# PhrameCMS Core

PhrameCMS Core is a plugin-first, API-oriented CMS runtime for PHP.

The design goal is to stay framework-neutral at extension boundaries while still allowing practical third-party internals.

## Principles

- Small core runtime.
- Optional features are plugins.
- Plugin contracts are stable and vendor-agnostic.
- Plugins are distributed as Composer packages.

## What Is Included In v0

- Minimal HTTP kernel and router.
- Service-provider plugin model.
- DI tag mechanism for extension points.
- Composer metadata plugin discovery.
- Built-in endpoints:
  - `GET /health`
  - `GET /capabilities`

## Quick Start

1. Install dependencies.

```bash
composer install
```

Create local environment file:

```bash
cp .env.example .env
```

2. (Optional) Install the example WYSIWYG plugin.

```bash
composer require phramecms/wysiwyg:*
```

3. Start the local server.

```bash
composer serve
```

4. Test endpoints.

```bash
curl http://127.0.0.1:8080/health
curl http://127.0.0.1:8080/capabilities
curl http://127.0.0.1:8080/plugins/wysiwyg/status
```

## Plugin Contract

Plugins publish a Composer `extra.phramecms` section.

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

- `provider` (string) or `providers` (array): service provider class(es).
- `capabilities` (array): optional capabilities reported by `/capabilities`.

A service provider must implement `PhrameCMS\\Core\\Contracts\\ServiceProviderInterface`.

To contribute routes, register a route provider class and tag it as `route.provider`.

## Notes

- This repository currently contains one reference plugin package in `packages/wysiwyg`.
- The reference plugin is optional and is not part of core runtime logic.

## Dependency Injection Engine

Core resolves its container through `ContainerFactory`, with all runtime/plugin code targeting `PhrameCMS\Core\Contracts\ContainerBuilderInterface`.

DependencyInjection provider support is implemented through the external `phramecms/dependency-injection-bridge` package.

You can override container engine selection with `PHRAME_CONTAINER`:

- `native`: force core native container.
- `dependency-injection`: force dependency-injection bridge container (fails if unavailable).
- aliases `symfony` and `symfony-di` are also supported for compatibility.
- `Fully\\Qualified\\ClassName`: instantiate a custom class that implements `PhrameCMS\Core\Contracts\ContainerBuilderInterface`.

Invalid custom class values are fail-fast: startup throws a runtime exception if the class does not exist, cannot be instantiated, or does not implement `ContainerBuilderInterface`.

If `PHRAME_CONTAINER` is not set, factory behavior is automatic: prefer bridge containers discovered by the factory and fall back to native container when no bridge implementation is available.

## Routing Engine

Core resolves route dispatch through a routing engine abstraction.

Routing bridge support is available through the external `phramecms/routing-bridge` package.

You can override routing engine selection with `PHRAME_ROUTING_ENGINE`:

- `native`: force core native route matching.
- `routing-bridge`: force preferred routing bridge discovery.
- aliases `symfony-routing`, `symfony`, and `routing` are also supported for compatibility.
- `Fully\\Qualified\\ClassName`: instantiate a custom class that implements `PhrameCMS\Core\Contracts\RoutingEngineInterface`.

If `PHRAME_ROUTING_ENGINE` is not set, factory behavior is automatic: prefer bridge routing engines discovered by the factory and fall back to native routing when no bridge implementation is available.

## Symfony HttpFoundation Bridge

Core remains framework-neutral at plugin boundaries. Symfony HttpFoundation support is provided by the external `phramecms/http-foundation-bridge` package and is installed as a core dependency.

Core consumes the bridge through Composer dependency resolution (from the package registry/repository), not from a local in-repo bridge directory.

The bridge package depends on `symfony/http-foundation` and converts between Symfony request/response objects and core transport types.

The bridge layer keeps a swap seam for the future: plugin contracts still use `PhrameCMS\Core\Http\Request` and `PhrameCMS\Core\Http\Response`, not Symfony types.

The entrypoint resolves a `HttpTransportInterface` via `HttpTransportFactory`, so replacing HttpFoundation later is isolated to transport classes.

You can override transport selection with `PHRAME_HTTP_TRANSPORT`:

- `native`: force core native request/response transport.
- `http-foundation`: force HttpFoundation bridge transport.
- `Fully\\Qualified\\ClassName`: instantiate a custom class that implements `PhrameCMS\Core\Contracts\HttpTransportInterface`.

Invalid custom class values are fail-fast: startup throws a runtime exception if the class does not exist, cannot be instantiated, or does not implement `HttpTransportInterface`.

If `PHRAME_HTTP_TRANSPORT` is not set, factory behavior is automatic: prefer bridge transports discovered by the factory and fall back to native transport only when no bridge implementation is available.

Environment variables are loaded from `.env` at bootstrap via a dotenv bridge.

Core supports provider-agnostic dotenv bridge implementations through `PhrameCMS\Core\Contracts\DotenvBridgeInterface`.

You can override bridge selection with `PHRAME_DOTENV_BRIDGE`:

- `Fully\\Qualified\\ClassName`: instantiate a custom class that implements `PhrameCMS\Core\Contracts\DotenvBridgeInterface`.

If `PHRAME_DOTENV_BRIDGE` is not set, core attempts to use the default `phramecms/dotenv-bridge` package class when available.

Existing process-level environment variables take precedence over `.env` values.

## Database Adapter

Core resolves database access through `PhrameCMS\Core\Contracts\DatabaseAdapterInterface`.

Doctrine DBAL support is provided by the external `phramecms/doctrine-dbal-bridge` package.

You can override database adapter selection with `PHRAME_DATABASE`:

- `none`: disable database adapter registration.
- `native` and `off`: aliases for `none`.
- `bridge`: use preferred bridge discovery.
- `doctrine`, `dbal`, and `doctrine-dbal-bridge`: aliases for bridge mode.
- `Fully\Qualified\ClassName`: instantiate a custom class that implements `PhrameCMS\Core\Contracts\DatabaseAdapterInterface`.

If `PHRAME_DATABASE` is not set, factory behavior is automatic: prefer the Doctrine bridge when available, and leave the database adapter unregistered when no bridge implementation is available.

The Doctrine bridge reads common connection settings from environment variables:

- `DB_DRIVER` (defaults to `pdo_sqlite`)
- `DB_PATH` (used by sqlite; defaults to `:memory:`)
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` (used by networked drivers)
