<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Controller\NativeControllerResolver;
use PhrameCMS\Core\CoreContainer;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;

final class NativeControllerResolverTest extends TestCase
{
    public function testResolveInvokableControllerClass(): void
    {
        $container = new CoreContainer();
        $resolver = new NativeControllerResolver();

        $handler = $resolver->resolve(NativeInvokableControllerForTest::class, $container);
        $response = $handler(new Request(HttpMethod::GET, '/controller', [], [], null), $container);

        self::assertSame(200, $response->status());
        self::assertSame('<h1>invokable</h1>', $response->body());
    }

    public function testResolveClassMethodFromContainerService(): void
    {
        $container = new CoreContainer();
        $container->set(NativeMethodControllerForTest::class, static fn (): NativeMethodControllerForTest => new NativeMethodControllerForTest());

        $resolver = new NativeControllerResolver();
        $handler = $resolver->resolve(NativeMethodControllerForTest::class . '::show', $container);

        $response = $handler(new Request(HttpMethod::GET, '/show', [], [], null), $container);

        self::assertSame(200, $response->status());
        self::assertStringContainsString('/show', $response->body());
    }

    public function testResolveThrowsWhenReturnTypeIsInvalid(): void
    {
        $container = new CoreContainer();
        $resolver = new NativeControllerResolver();

        $handler = $resolver->resolve(NativeInvalidReturnControllerForTest::class, $container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must return');

        $handler(new Request(HttpMethod::GET, '/invalid', [], [], null), $container);
    }
}

final class NativeInvokableControllerForTest
{
    public function __invoke(): Response
    {
        return Response::html('<h1>invokable</h1>');
    }
}

final class NativeMethodControllerForTest
{
    public function show(Request $request): Response
    {
        return Response::html('<h1>' . $request->path . '</h1>');
    }
}

final class NativeInvalidReturnControllerForTest
{
    public function __invoke(): string
    {
        return 'invalid';
    }
}
