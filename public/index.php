<?php

declare(strict_types=1);

use PhrameCMS\Core\Application;
use PhrameCMS\Core\Env\DotenvBridgeFactory;
use PhrameCMS\Core\Http\HttpTransportFactory;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'Composer autoload not found. Run composer install first.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(1);
}

require $autoload;

$envPath = dirname(__DIR__) . '/.env';
DotenvBridgeFactory::loadEnv($envPath);

$app = Application::bootFromComposer();
$transport = HttpTransportFactory::createDefault();
$response = $app->handle($transport->captureRequest());
$transport->emitResponse($response);
