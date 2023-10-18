<?php

declare(strict_types=1);

use Framework\{TemplateEngine, Database, Container};
use App\Config\Paths;
use App\Services\{ValidatorService, UserService};

return [
    TemplateEngine::class => fn () => new TemplateEngine(Paths::VIEWS),
    ValidatorService::class => fn () => new ValidatorService(),
    Database::class => fn () => new Database($_ENV["DB_DRIVER"], [
        "host" => $_ENV["DB_HOST"],
        "port" => $_ENV["DB_PORT"],
        "dbname" => $_ENV["DB_NAME"],
    ], $_ENV["DB_USER"], $_ENV["DB_PASS"]),
    // Since the container only resolves dependencies for controllers and middlewares (see router) we have to resolve the dependencies manually for UserService. For this, the container has to be passed to the factory function.
    UserService::class => function (Container $container) {
        $db = $container->get(Database::class);
        return new UserService($db);
    }
]; // Returns an associative array with class names as keys and functions returning instances as the values
