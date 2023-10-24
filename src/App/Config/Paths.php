<?php

declare(strict_types=1);

namespace App\Config;

// Set the directory or path for the template engine ("views");
class Paths
{
    public const VIEWS = __DIR__ . "/../views";
    public const SOURCE = __DIR__ . "/../../";
    public const ROOT = __DIR__ . "/../../../";
    public const STORAGE_UPLOADS = __DIR__ . "/../../../storage/uploads";
}
// Constants like this do not need to be defined in a class, but by defining it in a class, we can access it through Composer's auto-loading.