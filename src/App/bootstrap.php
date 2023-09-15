<?php

declare(strict_types=1);

include __DIR__ . "/../../vendor/autoload.php";

use Framework\App;
use App\Controllers\{HomeController, AboutController};

$app = new App();

$app->get("/", [HomeController::class, "home"]);
// In the above line we pass the class and method name instead of an instance so that an instance is only created if the route is accessed. This way we don't waste resources on unnecessary instances.
$app->get("/about", [AboutController::class, "about"]);

return $app;
