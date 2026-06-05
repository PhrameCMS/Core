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
3. Tag the service with `route.provider`.

## Capability Reporting

Declare capability strings in `extra.phramecms.capabilities`.

These strings are exposed by `GET /capabilities` so clients can detect available features dynamically.

## Compatibility Guidance

- Treat provider class names and behavior as part of your public plugin API.
- Follow semantic versioning.
- Keep cross-plugin coupling low by targeting core contracts only.
