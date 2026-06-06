<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Controller;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\ControllerResolverInterface;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Throwable;

final class NativeControllerResolver implements ControllerResolverInterface
{
    public function resolve(string $controllerReference, ContainerBuilderInterface $container): callable
    {
        $normalizedReference = trim($controllerReference);
        if ($normalizedReference === '') {
            throw new RuntimeException('Controller reference cannot be empty.');
        }

        [$className, $methodName] = $this->parseReference($normalizedReference);

        if (!class_exists($className)) {
            throw new RuntimeException(sprintf('Controller class "%s" was not found.', $className));
        }

        if (!method_exists($className, $methodName)) {
            throw new RuntimeException(sprintf('Controller method "%s::%s" was not found.', $className, $methodName));
        }

        $reflectionMethod = new ReflectionMethod($className, $methodName);
        if (!$reflectionMethod->isPublic()) {
            throw new RuntimeException(sprintf('Controller method "%s::%s" must be public.', $className, $methodName));
        }

        $controller = $this->resolveController($className, $container);

        return function (Request $request, ContainerBuilderInterface $runtimeContainer) use ($controller, $reflectionMethod): Response {
            $arguments = $this->resolveArguments($reflectionMethod, $request, $runtimeContainer);

            try {
                $result = $reflectionMethod->invokeArgs($controller, $arguments);
            } catch (Throwable $exception) {
                throw new RuntimeException($exception->getMessage(), (int) $exception->getCode(), $exception);
            }

            if (!$result instanceof Response) {
                throw new RuntimeException(sprintf(
                    'Controller action "%s::%s" must return %s.',
                    $reflectionMethod->getDeclaringClass()->getName(),
                    $reflectionMethod->getName(),
                    Response::class,
                ));
            }

            return $result;
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseReference(string $controllerReference): array
    {
        if (!str_contains($controllerReference, '::')) {
            return [$controllerReference, '__invoke'];
        }

        $parts = explode('::', $controllerReference, 2);
        $className = trim($parts[0]);
        $methodName = trim($parts[1]);

        if ($className === '' || $methodName === '') {
            throw new RuntimeException(sprintf('Invalid controller reference "%s".', $controllerReference));
        }

        return [$className, $methodName];
    }

    private function resolveController(string $className, ContainerBuilderInterface $container): object
    {
        if ($container->has($className)) {
            $resolved = $container->get($className);
            if (!is_object($resolved)) {
                throw new RuntimeException(sprintf('Controller service "%s" must resolve to an object.', $className));
            }

            return $resolved;
        }

        try {
            return new $className();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf(
                'Controller "%s" could not be instantiated (%s).',
                $className,
                $exception->getMessage(),
            ), (int) $exception->getCode(), $exception);
        }
    }

    /**
     * @return array<int, mixed>
     */
    private function resolveArguments(
        ReflectionMethod $method,
        Request $request,
        ContainerBuilderInterface $container,
    ): array {
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            $arguments[] = $this->resolveArgument($method, $parameter, $request, $container);
        }

        return $arguments;
    }

    private function resolveArgument(
        ReflectionMethod $method,
        ReflectionParameter $parameter,
        Request $request,
        ContainerBuilderInterface $container,
    ): mixed {
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $typeName = $type->getName();
            if (is_a($typeName, Request::class, true)) {
                return $request;
            }

            if (is_a($typeName, ContainerBuilderInterface::class, true)) {
                return $container;
            }

            if ($container->has($typeName)) {
                return $container->get($typeName);
            }
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException(sprintf(
            'Controller argument "$%s" in "%s::%s" could not be resolved.',
            $parameter->getName(),
            $method->getDeclaringClass()->getName(),
            $method->getName(),
        ));
    }
}