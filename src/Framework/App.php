<?php

declare(strict_types=1);

namespace Framework;

class App
{
  private Router $router;
  private Container $container;

  public function __construct(string $containerDefinitionsPath = null)
  {
    $this->router = new Router();
    $this->container = new Container();
    if ($containerDefinitionsPath) {
      $containerDefinitions = include $containerDefinitionsPath;
      $this->container->addDefinitions($containerDefinitions);
    }
  }

  public function get(string $path, array $controller): App // Necessary because the router is private and so this function is not accessible otherwise. It involves a little duplication of code, but this is worth it to keep the router protected.
  {
    $this->router->add("GET", $path, $controller);
    return $this; // By returning the App we can chain the add method (below) to a route registration.
  }

  public function post(string $path, array $controller): App
  {
    $this->router->add("POST", $path, $controller);
    return $this;
  }

  public function delete(string $path, array $controller): App
  {
    $this->router->add("DELETE", $path, $controller);
    return $this;
  }

  public function addMiddleware(string $middleware)
  {
    $this->router->addMiddleware($middleware);
  }

  public function run()
  {
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $method = $_SERVER["REQUEST_METHOD"];
    $this->router->dispatch($path, $method, $this->container);
  }

  public function add(string $middleware)
  {
    $this->router->addRouteMiddleware($middleware);
  }
}
