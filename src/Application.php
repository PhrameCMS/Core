<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\ControllerResolverInterface;
use PhrameCMS\Core\Contracts\RouteProviderInterface;
use PhrameCMS\Core\Contracts\RoutingEngineInterface;
use PhrameCMS\Core\Contracts\ServiceTag;
use PhrameCMS\Core\Contracts\ServiceProviderInterface;
use PhrameCMS\Core\Contracts\TemplateRendererInterface;
use PhrameCMS\Core\Controller\ControllerResolverFactory;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;
use PhrameCMS\Core\Plugin\PluginManager;
use PhrameCMS\Core\Plugin\PluginDefinition;
use PhrameCMS\Core\Routing\Route;
use PhrameCMS\Core\Routing\RoutingEngineFactory;
use PhrameCMS\Core\Template\TemplateRendererFactory;
use RuntimeException;

final class Application
{
    /**
     * @var array<int, Route>
     */
    private array $routes = [];

    /**
     * @var array<int, ServiceProviderInterface>
     */
    private array $providers = [];

    private readonly RoutingEngineInterface $routingEngine;
    private readonly ControllerResolverInterface $controllerResolver;

    public function __construct(
        private readonly ContainerBuilderInterface $container,
        private readonly PluginManager $pluginManager,
    ) {
        $this->routingEngine = RoutingEngineFactory::createDefault();
        $this->controllerResolver = ControllerResolverFactory::createDefault();
    }

    public static function bootFromComposer(): self
    {
        $app = new self(ContainerFactory::createDefault(), new PluginManager());
        $app->bootstrap();

        return $app;
    }

    public function bootstrap(): void
    {
        $this->registerCoreServices();
        $this->registerCoreRoutes();

        $plugins = $this->pluginManager->discover();
        $this->container->set('plugins', $plugins);

        /** @var CapabilityRegistry $capabilityRegistry */
        $capabilityRegistry = $this->container->get(CapabilityRegistry::class);
        foreach ($plugins as $plugin) {
            $capabilityRegistry->addMany($plugin->capabilities);

            foreach ($plugin->providers as $providerClass) {
                $provider = $this->resolveProvider($providerClass);
                $provider->register($this->container);
                $this->providers[] = $provider;
            }
        }

        $this->collectPluginRoutes();
        $this->collectManifestControllerRoutes($plugins);

        foreach ($this->providers as $provider) {
            $provider->boot($this->container);
        }
    }

    public function handle(Request $request): Response
    {
        $response = $this->routingEngine->dispatch($request, $this->routes, $this->container);
        if ($response !== null) {
            return $response;
        }

        return Response::json([
            'error' => 'Not Found',
            'path' => $request->path,
        ], 404);
    }

    private function registerCoreServices(): void
    {
        $this->container->set(CapabilityRegistry::class, static fn (): CapabilityRegistry => new CapabilityRegistry());
        $this->container->set(ControllerResolverInterface::class, $this->controllerResolver);

        $templateRenderer = TemplateRendererFactory::createDefault();
        if ($templateRenderer !== null) {
            $this->container->set(TemplateRendererInterface::class, $templateRenderer);
        }

        $this->container->set('core.version', '0.1.0-dev');
    }

    private function registerCoreRoutes(): void
    {
        $this->addRoute(Route::create(HttpMethod::GET, '/health', function (): Response {
            $version = $this->container->get('core.version');
            if (!is_string($version)) {
                throw new RuntimeException('Core version must be a string.');
            }

            return Response::json([
                'status' => 'ok',
                'service' => 'phramecms-core',
                'version' => $version,
            ]);
        }));

        $this->addRoute(Route::create(HttpMethod::GET, '/capabilities', function (): Response {
            /** @var CapabilityRegistry $capabilities */
            $capabilities = $this->container->get(CapabilityRegistry::class);

            return Response::json([
                'capabilities' => $capabilities->all(),
            ]);
        }));
    }

    private function collectPluginRoutes(): void
    {
        $providerIds = $this->container->tagged(ServiceTag::RouteProvider);

        foreach ($providerIds as $providerId) {
            $provider = $this->container->get($providerId);
            if (!$provider instanceof RouteProviderInterface) {
                throw new RuntimeException(sprintf(
                    'Tagged service "%s" must implement %s.',
                    $providerId,
                    RouteProviderInterface::class,
                ));
            }

            foreach ($provider->routes() as $route) {
                $this->addRoute($route);
            }
        }
    }

    /**
     * @param array<int, PluginDefinition> $plugins
     */
    private function collectManifestControllerRoutes(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            foreach ($plugin->controllers as $controllerRoute) {
                $handler = $this->controllerResolver->resolve($controllerRoute->controller, $this->container);
                $route = Route::create($controllerRoute->method, $controllerRoute->path, $handler);

                $this->addRoute($route);
            }
        }
    }

    private function addRoute(Route $route): void
    {
        foreach ($this->routes as $existingRoute) {
            if ($existingRoute->method === $route->method && $existingRoute->path === $route->path) {
                throw new RuntimeException(sprintf(
                    'Duplicate route definition for %s %s.',
                    $route->method->value,
                    $route->path,
                ));
            }
        }

        $this->routes[] = $route;
    }

    private function resolveProvider(string $providerClass): ServiceProviderInterface
    {
        if (!class_exists($providerClass)) {
            throw new RuntimeException(sprintf('Plugin provider class "%s" was not found.', $providerClass));
        }

        if (!$this->container->has($providerClass)) {
            $this->container->set($providerClass, static fn () => new $providerClass());
        }

        $provider = $this->container->get($providerClass);
        if (!$provider instanceof ServiceProviderInterface) {
            throw new RuntimeException(sprintf(
                'Plugin provider "%s" must implement %s.',
                $providerClass,
                ServiceProviderInterface::class,
            ));
        }

        return $provider;
    }
}
