# LumeCMS Core

LumeCMS Core is a plugin-first, API-oriented CMS runtime for PHP.

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

2. (Optional) Install the example WYSIWYG plugin.

```bash
composer require lumecms/wysiwyg-placeholder:*
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

Plugins publish a Composer `extra.lume` section.

```json
{
  "extra": {
    "lume": {
      "provider": "Vendor\\Package\\MyServiceProvider",
      "capabilities": ["feature.example"]
    }
  }
}
```

- `provider` (string) or `providers` (array): service provider class(es).
- `capabilities` (array): optional capabilities reported by `/capabilities`.

A service provider must implement `Lume\\Core\\Contracts\\ServiceProviderInterface`.

To contribute routes, register a route provider class and tag it as `route.provider`.

## Notes

- This repository currently contains one reference plugin package in `packages/wysiwyg-placeholder`.
- The reference plugin is optional and is not part of core runtime logic.
