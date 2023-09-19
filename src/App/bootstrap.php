<?php

declare(strict_types=1);

include __DIR__ . "/../../vendor/autoload.php";

use Framework\App;
use function App\Config\registerRoutes; // This is how we can import a function

$app = new App();

registerRoutes($app);

return $app;
