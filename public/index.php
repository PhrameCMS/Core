<?php

declare(strict_types=1);

use Lume\Core\Application;
use Lume\Core\Http\Request;

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

$app = Application::bootFromComposer();
$response = $app->handle(Request::fromGlobals());
$response->send();
