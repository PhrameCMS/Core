<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\RouteProviderInterface;
use PhrameCMS\Core\Contracts\ServiceTag;
use PhrameCMS\Core\Contracts\ServiceProviderInterface;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;
use PhrameCMS\Core\Plugin\PluginManager;
use PhrameCMS\Core\Routing\Route;
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

    public function __construct(
        private readonly ContainerBuilderInterface $container,
        private readonly PluginManager $pluginManager,
    ) {
    }

    public static function bootFromComposer(): self
    {
        $app = new self(new CoreContainer(), new PluginManager());
        $app->bootstrap();

        return $app;
    }

    public function bootstrap(): void
    {
        $this->registerCoreServices();
        $this->registerCoreRoutes();

        $plugins = $this->pluginManager->discover();
        $this->container->set('plugins', $plugins);

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

        foreach ($this->providers as $provider) {
            $provider->boot($this->container);
        }
    }

    public function handle(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if (!$route->matches($request->method, $request->path)) {
                continue;
            }

            return ($route->handler)($request, $this->container);
        }

        return Response::json([
            'error' => 'Not Found',
            'path' => $request->path,
        ], 404);
    }

    private function registerCoreServices(): void
    {
        $this->container->set(CapabilityRegistry::class, static fn (): CapabilityRegistry => new CapabilityRegistry());
        $this->container->set('core.version', '0.1.0-dev');
    }

    private function registerCoreRoutes(): void
    {
        $this->routes[] = Route::create(HttpMethod::GET, '/health', function (): Response {
            return Response::json([
                'status' => 'ok',
                'service' => 'phramecms-core',
                'version' => (string) $this->container->get('core.version'),
            ]);
        });

        $this->routes[] = Route::create(HttpMethod::GET, '/capabilities', function (): Response {
            /** @var CapabilityRegistry $capabilities */
            $capabilities = $this->container->get(CapabilityRegistry::class);

            return Response::json([
                'capabilities' => $capabilities->all(),
            ]);
        });
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
                $this->routes[] = $route;
            }
        }
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
