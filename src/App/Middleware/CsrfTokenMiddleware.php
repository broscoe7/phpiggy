<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use Framework\TemplateEngine;

class CsrfTokenMiddleware implements MiddlewareInterface
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function process(callable $next)
    {
        $_SESSION["token"] = $_SESSION["token"] ?? bin2hex(random_bytes(32));
        // Random_bytes creates a random binary number, then bin2hex translates that into a hexidecimal string so that it can be viewable in the browser. The $_SESSION["token"] ?? allows the token to persist instead of being regenerated for every request.
        $this->view->addGlobal("csrfToken", $_SESSION["token"]);
        $next();
    }
}
