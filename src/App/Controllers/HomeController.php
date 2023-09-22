<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\TemplateEngine;
use App\Config\Paths;

class HomeController
{
    public function __construct(private TemplateEngine $view)
    {
    }
    public function home(): void
    {
        echo $this->view->render("index.php"); // This is how we pass data to the template engine
    }
}
