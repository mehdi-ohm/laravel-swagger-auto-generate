<?php

namespace G4T\Swagger\Responses;

use function Clue\StreamFilter\fun;

class MethodResponse
{
    protected static array $defaultResponses;

    public function __construct()
    {
        self::getDefaultResponses();
    }

    public static function index($route)
    {
        // Options
        $onlyJsonRequest = config('swagger.options.only_json_request') ?: false;

        // Prepare the swagger path
        $response = [
            "tags" => [
                $route['controller'],
            ],
            "summary" => self::getSummary($route),
            "description" => $route['description'] ?: '',
            "operationId" => $route['operation_id'],
            "parameters" => $route['params'],
            "requestBody" => [
                "description" => $route['request_description'] ?: '',
                "content" => self::getRequestContent($route, $onlyJsonRequest),
                "required" => true
            ],
            "responses" => $route['responses'] ?: self::getResponses($route['method'], $route['uri']),
            "security" => config('swagger.security_schemes'),
        ];

        // Remove empty parameters
        if (count($route['params']) == 0) {
            unset($response['parameters']);
        }

        // Remove request body
        if (empty($route['has_schema']) or $route['method'] === 'GET') {
            unset($response['requestBody']);
        }

        if ($route['need_token']) {
            $security_array = [];
            $security_schemes = config('swagger.security_schemes');
            foreach ($security_schemes as $key => $security_scheme) {
                $security_array[] = [
                    $key => []
                ];
            }
            $response['security'] = $security_array;
        } else {
            unset($response['security']);
        }

        $enable_response_schema = config('swagger.enable_response_schema');
        if ($enable_response_schema) {
            $dir = str_replace(['/', '{', '}', '?'], '-', $route['uri']);
            $jsonDirPath = storage_path("swagger/{$route['controller']}/{$dir}");
            if (is_dir($jsonDirPath)) {
                $files = glob($jsonDirPath . '/*.json');
                foreach ($files as $file) {
                    $parts = explode('/', rtrim($file, '/'));
                    $lastPart = end($parts);
                    if (preg_match('/(\d+)\.json$/', $lastPart, $matches)) {
                        $statusCode = $matches[1];
                        $jsonContent = json_decode(file_get_contents($file), true);
                        $response["responses"]["$statusCode"]["description"] = $jsonContent['status_text'];
                        $response["responses"]["$statusCode"]["content"]["application/json"]["example"] = $jsonContent['response'];
                    }
                }
            }
        }
        return $response;
    }

    protected static function getRequestContent(array $route, bool $onlyJsonRequest = false): array
    {
        $requestBodyContent = [
            "application/json" => [
                "schema" => [
                    '$ref' => "#/components/schemas/{$route['schema_name']}"
                ],
            ],
        ];

        if (false === $onlyJsonRequest) {
            $requestBodyContent[self::getDefaultMimeTypeByHttpMethod($route['method'])] = [
                "schema" => [
                    '$ref' => "#/components/schemas/{$route['schema_name']}"
                ],
            ];
        }

        return $requestBodyContent;
    }

    protected static function getSummary(array $route): string
    {
        return $route['summary'] ?? $route['name'] ?? '';
    }

    protected static function getDefaultMimeTypeByHttpMethod(string $method): string
    {
        switch ($method) {
            case 'POST':
            case 'PUT':
                return 'multipart/form-data';
            case 'GET':
            case 'PATCH':
            case 'DELETE':
            default:
                return 'application/x-www-form-urlencoded';
        }
    }

    public static function getResponses(string $mehtod, string $name, array $codes = [200, 404, 422, 503]): array
    {
        if ($responses = self::getDefaultResponses($mehtod)) {
            return $responses;
        }

        $responses = [
            200 => [
                "description" => "Successful operation",
            ],
            401 => [
                "description" => "Unauthorized",
            ],
            403 => [
                "description" => "Forbidden",
            ],
            404 => [
                "description" => "The route ".$name." is not found",
            ],
            422 => [
                "description" => "Validation Issues",
            ],
            503 => [
                "description" => "Service Unavailable",
            ],
        ];

        foreach ($responses as $code => $response) {
            if (!in_array($code, $codes)) {
                unset($responses[$code]);
            }
        }

        return $responses;
    }

    public static function getDefaultResponses(?string $method = null): ?array
    {
        if (!isset(self::$defaultResponses)) {
            self::$defaultResponses = config('swagger.status') ?: [];
        }

        if ($method) {
            return self::$defaultResponses[$method] ?? null;
        }

        return self::$defaultResponses;
    }
}
