<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\TemplateEngine;
use App\Config\Paths;

class HomeController
{
    private TemplateEngine $view;

    public function __construct()
    {
        $this->view = new TemplateEngine(Paths::VIEWS);
    }
    public function home(): void
    {
        echo $this->view->render("index.php", ["title" => "Home Page"]); // This is how we pass data to the template engine
    }
}
