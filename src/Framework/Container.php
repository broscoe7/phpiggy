<?php

declare(strict_types=1);

namespace Framework;

use ReflectionClass, ReflectionNamedType;
use Framework\Exceptions\ContainerException;

class Container
{
    private array $definitions = [];
    private array $resolved = [];

    public function addDefinitions(array $newDefinitions)
    {
        $this->definitions = [...$this->definitions, ...$newDefinitions];
    }

    public function resolve(string $className)
    {
        $reflectionClass = new ReflectionClass($className);

        // Check to make sure class is not an abstract class
        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException("Class {$className} is not instantiable.");
        }

        $constructor = $reflectionClass->getConstructor();
        // Check if the class has a constructor method (if it does not have dependencies it might not have a constructor method)
        if (!$constructor) {
            return new $className;
        }
        // Retrieve the parameters of the constructor method
        $params = $constructor->getParameters();
        // Check if the constructor method has parameters
        if (count($params) === 0) {
            return new $className;
        }
        $dependencies = [];
        foreach ($params as $param) {
            $name = $param->getName();
            $type = $param->getType();
            // Enforce type-hinting
            if (!$type) {
                throw new ContainerException("Failed to resolve class {$className} because param {$name} is missing a type hint.");
            }
            // Check that the parameter is a single type (ReflectionNamedType) and that it is not a built-in type (like string, boolean, etc.) since it must be a class/instance (type TemplateEngine).
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new ContainerException("Failed to resolve class {$className} because of invalid parameter.");
            }
            $dependencies[] = $this->get($type->getName());
        }
        return $reflectionClass->newInstanceArgs($dependencies);
    }
    // The "get" function instantiates and returns the required dependency
    public function get(string $id)
    {
        if (!array_key_exists($id, $this->definitions)) {
            throw new ContainerException("Class {$id} does not exist in container.");
        }

        // If instance already exists, return it.
        if (array_key_exists($id, $this->resolved)) return $this->resolved[$id];

        $factory = $this->definitions[$id];
        $dependency = $factory($this); // Passes container to the factory function so that dependencies can be grabbed manually
        $this->resolved[$id] = $dependency; // Keeping track of which class already have an instance created.
        return $dependency;
    }
}
