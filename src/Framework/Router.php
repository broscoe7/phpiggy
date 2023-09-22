<?php

declare(strict_types=1);

namespace Framework;

class Router
{
  private array $routes = [];
  private array $middlewares = [];

  public function add(string $method, string $path, array $controller): void
  {
    $path = $this->normalizePath($path);
    $this->routes[] = [
      "path" => $path,
      "method" => strtoupper($method),
      "controller" => $controller,
    ];
  }

  private function normalizePath(string $path): string
  {
    $path = trim($path, "/");
    $path = "/{$path}/";
    $path = preg_replace("#[/]{2,}#", "/", $path); // Because otherwise the root path "/" will be deleted and transformed into "//" by the normalizePath function
    return $path;
  }

  public function dispatch(string $path, string $method, Container $container = null)
  {
    $path = $this->normalizePath($path);
    $method = strtoupper($method);
    foreach ($this->routes as $route) {
      if (
        !preg_match("#^{$route['path']}$#", $path)
        || $route['method'] !== $method
      ) continue;

      [$class, $function] = $route["controller"];
      $controllerInstance = $container ? $container->resolve($class) : new $class();
      $action = fn () => $controllerInstance->{$function}();

      // Activate Middlewares
      foreach ($this->middlewares as $middleware) {
        $middlewareInstance = $container ? $container->resolve($middleware) : new $middleware;
        $action = fn () => $middlewareInstance->process($action);
      }
      $action();

      return;  // This prevents another route from becoming active.
    }
  }

  public function addMiddleware(string $middleware)
  {
    $this->middlewares[] = $middleware;
  }
}
