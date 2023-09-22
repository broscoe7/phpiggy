<?php

declare(strict_types=1);

include __DIR__ . "/../../vendor/autoload.php";

use Framework\App;
use App\Config\Paths;
use function App\Config\{registerRoutes, registerMiddleware}; // This is how we can import a function

$app = new App(Paths::SOURCE . "App/container-definitions.php");

registerMiddleware($app);
registerRoutes($app);

return $app;
