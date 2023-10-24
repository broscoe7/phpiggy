<?php

declare(strict_types=1);

namespace Framework;

class Router
{
  private array $routes = [];
  private array $middlewares = [];
  private array $errorHandler;

  public function add(string $method, string $path, array $controller): void
  {
    $path = $this->normalizePath($path);
    $regexPath = preg_replace("#{[^/]+}#", "([^/]+)", $path); // The parentheses allow us to select multiple values from a single string
    $this->routes[] = [
      "path" => $path,
      "method" => strtoupper($method),
      "controller" => $controller,
      "middleware" => [],  // Allows us to set specific middleware to a route so we can control which routes are accessible through authentication
      "regexPath" => $regexPath
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
    $method = strtoupper($_POST["_METHOD"] ?? $method); // First checks to see if there is a hidden input defining a different method (e.g. put, delete, etc.)
    foreach ($this->routes as $route) {
      if (
        !preg_match("#^{$route['regexPath']}$#", $path, $paramValues) // Third parameter stores the values that match the criteria - the first item is the full string; the second item is the match.
        || $route['method'] !== $method
      ) continue;

      // Get parameter value
      array_shift($paramValues);

      // Extract the route parameter placeholder to act as key name for the parameter value
      preg_match_all("#{([^/]+)}#", $route["path"], $paramKeys);
      $paramKeys = $paramKeys[1];

      // Combine route parameter key(s) and value(s) into a new array
      $params = array_combine($paramKeys, $paramValues);

      [$class, $function] = $route["controller"];
      $controllerInstance = $container ? $container->resolve($class) : new $class();
      $action = fn () => $controllerInstance->{$function}($params);

      // Merge middleware list with route specific middlewares
      $allMiddlewares = [...$route["middleware"], ...$this->middlewares]; // Apply global middleware last since it gets executed first.

      // Activate Middlewares
      foreach ($allMiddlewares as $middleware) {
        $middlewareInstance = $container ? $container->resolve($middleware) : new $middleware;
        $action = fn () => $middlewareInstance->process($action);
      }
      $action();

      return;  // This prevents another route from becoming active.
    }
    $this->dispatchNotFound($container);
  }

  public function addMiddleware(string $middleware)
  {
    $this->middlewares[] = $middleware;
  }

  public function addRouteMiddleware(string $middleware)
  {
    $lastRouteKey = array_key_last($this->routes);
    $this->routes[$lastRouteKey]["middleware"][] = $middleware;
  }

  public function setErrorHandler(array $controller)
  {
    $this->errorHandler = $controller;
  }

  public function dispatchNotFound(?Container $container)
  {
    [$class, $function] = $this->errorHandler;
    // Instantiate controller
    $controllerInstance = $container ? $container->resolve($class) : new $class;
    // Invoke controller from the controller instance
    $action = fn () => $controllerInstance->$function();

    // Activate middlewares
    foreach ($this->middlewares as $middleware) {
      $middlewareInstance = $container ? $container->resolve($middleware) : new $middleware;
      $action = fn () => $middlewareInstance->process($action);
    }
    $action();
  }
}
