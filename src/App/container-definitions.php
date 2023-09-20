<?php

declare(strict_types=1);

use Framework\TemplateEngine;
use App\Config\Paths;

return [
    TemplateEngine::class => fn () => new TemplateEngine(Paths::VIEWS)
]; // Returns an associative array with class names as keys and functions returning instances as the values
