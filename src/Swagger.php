<?php

namespace G4T\Swagger;

use G4T\Swagger\Attributes\SwaggerSection;
use G4T\Swagger\Requests\Contracts\RequestDocSwagger;
use G4T\Swagger\Responses\Contracts\ResponseDocSwagger;
use G4T\Swagger\Sections\Paths;
use G4T\Swagger\Sections\Schemas;
use G4T\Swagger\Sections\Tags;
use G4T\Swagger\Utils\MethodInspector;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Routing\Route as IlluminateRoute;
use ReflectionClass;
use stdClass;

class Swagger
{

    use Tags, Paths, Helpers, Schemas;

    /**
     * Generate the Swagger documentation.
     *
     * @return array
     */
    public function swagger()
    {
        $swaggerClass = $this->generateSwaggerJsonResponse();

        $swagger = [
            "openapi" => "3.0.3",
            "info" => [
                "title" => config('swagger.title'),
                "description" => config('swagger.description') ?: '',
                "termsOfService" => "http://swagger.io/terms/",
                "contact" => [
                    "email" => config('swagger.email'),
                ],
                "license" => [
                    "name" => "Github",
                    "url" => "https://github.com/hussein4alaa"
                ],
                "version" => config('swagger.version')
            ],
            "servers" => config('swagger.servers'),
            "tags" => $swaggerClass->tags,
            "paths" => $swaggerClass->paths,
        ];

        unset($swaggerClass->tags);
        unset($swaggerClass->paths);
        $swagger["components"] = $swaggerClass;

        return $swagger;
    }



    /**
     * Generate the Swagger JSON response for API documentation.
     *
     * This function retrieves all routes defined in the application and filters out the API routes. It collects relevant information for each API route, including prefix, action name, route name, HTTP method, URI, operation ID, validations, and schema name. The function determines if a route has a schema based on the presence of validations. It also checks if a token is required for the route.
     *
     * The collected information is used to build an array of API routes, which includes details such as the route's prefix, method, URI, name, schema name, action, middleware, validations, parameters, operation ID, whether it has a schema, and whether a token is required.
     *
     * Additionally, the function collects all the route names to generate tags for the Swagger documentation. It formats the collected API routes and constructs the JSON data structure required for Swagger documentation. This JSON data includes tags, paths, schemas, and security schemes.
     *
     * The function returns the generated Swagger JSON response as an object.
     *
     * @return object The Swagger JSON response for API documentation.
     */

    public function generateSwaggerJsonResponse()
    {
        $routes = Route::getRoutes();
        $apiRoutes = [];
        $names = [];
        $schemas = [];
        $show_prefix_array = config('swagger.show_prefix');
        $mapping_prefix = config('swagger.mapping_prefix');
        $controllers = [];

        $version = $this->getVersion();
        foreach ($routes as $route) {
            if ($this->isApiRoute($route)) {
                if (is_string($route->getAction('controller'))) {
                    $uri = '/' . $route->uri();
//                    dd($uri, $version, str_contains($uri, $version));
                    if (str_contains($uri, $version)) {
                        $prefix = $route->getPrefix();
                        $action = ltrim($route->getActionName(), '\\');
                        $controller = $this->getControllerName($route->getAction('controller'));
                        $routeName = $this->getRouteName($route->uri(), $prefix);
                        $method = implode('|', $route->methods());
                        $operationId = $this->generateOperationId($uri, $method);
                        $validations = $this->getRequestClassName($action);
                        $schemaName = $this->schemaName($action);
                        if ($action !== 'Closure') {
                            $description = $route->action['description'] ?? '';
                            $requestDescription = $route->action['request_description']
                                ?? $this->getRequestDescriptionFromControllerMethodParameters($route)
                                ?? $description;
                            $description = $description ?: $requestDescription;
                            $summary = isset($route->action['summary']) ? $route->action['summary'] : null;
                            $responses = $route->action['responses']
                                ?? $this->getSwaggerResponseFromControllerMethodReturn($route)
                                ?? [];
                            $prefix_for_condition = isset($show_prefix_array) && count($show_prefix_array) > 0 ? $show_prefix_array : ["$prefix"];
                            if (in_array($prefix, $prefix_for_condition)) {
                                $hasSchema = false;

                                if (isset($mapping_prefix[$prefix])) {
                                    $uri = str_replace($prefix, $mapping_prefix[$prefix], $uri);
                                    $prefix = $mapping_prefix[$prefix];
                                }

                                if (!is_null($validations) && count($validations) > 0) {
                                    $hasSchema = true;
                                    $schemas[$schemaName] = $this->getSchemas($validations, $schemaName, $method);
                                }

                                $needToken = $this->checkIfTokenIsRequired($route);
                                $controller_path = $this->getControllerPath($route->getAction('controller'));
                                $controller_description = $this->getSectionAttributeValue($controller_path);
                                $controllers[$controller] = [
                                    "name" => $controller,
                                    "class" => $controller_path,
                                    "description" => $controller_description
                                ];
                                $apiRoutes[] = [
                                    'prefix' => $prefix,
                                    'method' => $method,
                                    'controller' => $controller,
                                    'uri' => $uri,
                                    'description' => $description,
                                    'request_description' => $requestDescription,
                                    'summary' => $summary,
                                    'name' => $routeName,
                                    'schema_name' => $schemaName,
                                    'action' => $action,
                                    'middleware' => $route->middleware(),
                                    'validations' => $validations,
                                    'params' => $this->formatParams($validations, $route),
                                    'responses' => $responses,
                                    'operation_id' => $operationId,
                                    'has_schema' => $hasSchema,
                                    'need_token' => $needToken
                                ];
                            }
                        }
                    }
                }
            }
        }
        $swaggerJson = new stdClass();
        $swaggerJson->tags = $this->getTags($controllers);
        $swaggerJson->paths = $this->formatPaths($apiRoutes);
        $swaggerJson->schemas = $schemas;
        $swaggerJson->securitySchemes = config('swagger.security_schemes');

        return $swaggerJson;
    }


    public function getControllerPath($controller)
    {
        try {
            $controller = explode("@", $controller);
            return $controller[0];
        } catch (\Throwable $th) {
            return null;
        }
    }

    protected function getRequestDescriptionFromControllerMethodParameters(IlluminateRoute $route): ?string
    {
        if (empty($route->action['controller'])) {
            return null;
        }

        $types = MethodInspector::getParametersType($route->action['controller']);
        foreach ($types ?? [] as $typeText) {
            if ($typeText and !in_array($typeText, ['Illuminate\Http\Request']) and class_exists($typeText)) {

                $requester = new $typeText();
                if ($requester instanceof RequestDocSwagger) {
                    return $requester->getDescription() ?: null;
                }
            }
        }

        return null;
    }

    protected function getSwaggerResponseFromControllerMethodReturn(IlluminateRoute $route): ?array
    {
        if (empty($route->action['controller'])) {
            return null;
        }

        $types = MethodInspector::getReturnsType($route->action['controller']);
        foreach ($types ?? [] as $typeText) {
            if ($typeText and class_exists($typeText)) {

                $requester = new $typeText();
                if ($requester instanceof ResponseDocSwagger) {
                    return $requester->getSwaggerResponses() ?: null;
                }
            }
        }

        return null;
    }

    private function getSectionAttributeValue(string $controllerClassName)
    {
        try {
            $class = new $controllerClassName;
            $reflector = new ReflectionClass($class);
            $attributes = $reflector->getAttributes(SwaggerSection::class);
            if (!empty($attributes)) {
                $attribute = $attributes[0];
                return $attribute->newInstance()->getValue();
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            return null;
        }
    }

    private function getVersion()
    {
        $versions = config('swagger.versions');
        $version = 'api/';
        if (request()->filled('version') && in_array(request()->version, $versions)) {
            if(request()->version == 'all') {
                $version = null;
            } else {
                $version = 'api/' . request()->version;
            }
        }
        return $version;
    }
}
