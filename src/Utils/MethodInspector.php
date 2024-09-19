<?php

namespace G4T\Swagger\Utils;

use ReflectionMethod;
use ReflectionNamedType;

class MethodInspector
{
    /**
     * Retrieves the type of the parameters of the specified method.
     *
     * @param string $controllerAction Ex: 'App\Http\Controllers\MyController@myMethod'
     * @return array|null List of parameters's type or null if not declared
     */
    public static function getParametersType(string $controllerAction): ?array
    {
        list($controller, $method) = explode('@', $controllerAction);

        $reflectionMethod = new ReflectionMethod($controller, $method);
        $parameters = $reflectionMethod->getParameters();

        if (count($parameters) > 0) {
            $types = [];

            foreach ($parameters as $parameter) {
                if ($parameter->getType() instanceof ReflectionNamedType) {
                    $types[] = $parameter->getType()->getName();  // Append the name of the type (e.g., 'string', 'int', etc.)
                }
            }

            return $types;
        }

        return null;  // If no type is declared
    }

    /**
     * Retrieves the return type of the specified method.
     *
     * @param string $controllerAction Ex: 'App\Http\Controllers\MyController@myMethod'
     * @return array|null List of return's type or null if not declared
     */
    public static function getReturnsType(string $controllerAction): ?array
    {
        list($controller, $method) = explode('@', $controllerAction);

        $reflectionMethod = new ReflectionMethod($controller, $method);
        $returnType = $reflectionMethod->getReturnType();

        if ($returnType instanceof ReflectionNamedType) {
            return [
                $returnType->getName(),
            ];  // Returns the name of the return type (e.g., 'string', 'App\Models\User')
        }

        return null;  // If no return type is declared
    }
}