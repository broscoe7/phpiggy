<?php

declare(strict_types=1);

namespace App\Config;

use Framework\App;
use App\Controllers\{HomeController, AboutController, AuthController};
use App\Middleware\{AuthRequiredMiddleware, GuestOnlyMiddleware};

function registerRoutes(App $app)
{
    $app->get("/", [HomeController::class, "home"])->add(AuthRequiredMiddleware::class);
    // In the above line we pass the class and method name instead of an instance so that an instance is only created if the route is accessed. This way we don't waste resources on unnecessary instances.
    $app->get("/about", [AboutController::class, "about"]);
    $app->get("/register", [AuthController::class, "registerView"])->add(GuestOnlyMiddleware::class);
    $app->post("/register", [AuthController::class, "register"])->add(GuestOnlyMiddleware::class);
    $app->get("/login", [AuthController::class, "loginView"])->add(GuestOnlyMiddleware::class);
    $app->post("/login", [AuthController::class, "login"])->add(GuestOnlyMiddleware::class);
    $app->get("/logout", [AuthController::class, "logout"])->add(AuthRequiredMiddleware::class);
}
