<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Swagger
    |--------------------------------------------------------------------------
    |
    | Whether Swagger is enabled or not.
    |
    */
    "enable" => env('SWAGGER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Title
    |--------------------------------------------------------------------------
    |
    | The title of your API documentation.
    |
    */
    "title" => env("SWAGGER_TITLE", "Api Documentation"),

    /*
    |--------------------------------------------------------------------------
    | API Description
    |--------------------------------------------------------------------------
    |
    | The description of your API.
    |
    */
    "description" => env("SWAGGER_DESCRIPTION", "Laravel autogenerate swagger"),

    /*
    |--------------------------------------------------------------------------
    | API Email
    |--------------------------------------------------------------------------
    |
    | The email associated with your API documentation.
    |
    */
    "email" => env("SWAGGER_EMAIL", "hussein4alaa@gmail.com"),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | The version of your API.
    |
    */
    "version" => env("SWAGGER_VERSION", "3.0.7"),

    /*
    |--------------------------------------------------------------------------
    | Enable Response Schema
    |--------------------------------------------------------------------------
    |
    | Whether to enable response schema or not.
    |
    */
    "enable_response_schema" => true,

    "suggestions_select_input" => false,

    "load_from_json" => false,

    /*
    |--------------------------------------------------------------------------
    | Route middleware
    |--------------------------------------------------------------------------
    |
    | List of middleware used for all swagger route.
    | Use this for configure the swagger access rules.
    | Use this for dynamically change the swagger configuration, like to preauthorize.
    |
    */
    "routes" => [
        "middlewares" => [
            // Examples:
            // 'auth',
            // 'can:swagger-access',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Middlewares
    |--------------------------------------------------------------------------
    |
    | List of middleware names used for authentication.
    |
    */
    "auth_middlewares" => [
        "auth",
        "auth:api",
    ],

    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | The URL path for accessing your API documentation.
    |
    */
    "url" => env("SWAGGER_URL", "swagger/documentation"),

    /*
    |--------------------------------------------------------------------------
    | Issues URL
    |--------------------------------------------------------------------------
    |
    | The URL path for accessing issues related to your API documentation.
    |
    */
    "issues_url" => env("SWAGGER_ISSUE_URL", "swagger/issues"),

    /*
    |--------------------------------------------------------------------------
    | Show Prefix
    |--------------------------------------------------------------------------
    |
    | List of prefixes to show in Swagger.
    |
    */
    "show_prefix" => [],


    /*
    |--------------------------------------------------------------------------
    | API Versions
    |--------------------------------------------------------------------------
    |
    | List of versions to show in Swagger.
    |
    */
    "versions" => [
        "all",
        // "v1"
    ],

    "default" => "all",


    /*
    |--------------------------------------------------------------------------
    | Servers
    |--------------------------------------------------------------------------
    |
    | List of servers associated with your API.
    |
    */
    "servers" => [
        [
            "url" => env("APP_URL"),
            "description" => "localhost"
        ]
    ],


    /*
    |--------------------------------------------------------------------------
    | Security Schemes
    |--------------------------------------------------------------------------
    |
    | Security schemes used in your API.
    |
    */
    "security_schemes" => [
        "authorization" => [
            "type" => "apiKey",
            "name" => "authorization",
            "in" => "header"
        ],
        "apiKey1" => [
            "type" => "apiKey",
            "name" => "key1",
            "in" => "query"
        ]
    ],


    /*
    |--------------------------------------------------------------------------
    | Preauthorize ApiKey
    |--------------------------------------------------------------------------
    |
    | Configuration for preauthorizing API requests with an API key.
    |
    */
    'security_preauthorize' => [
        'api_key' => [
            'authorization' => null,
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Schema configuration
    |--------------------------------------------------------------------------
    |
    | Schema configuration options
    |
    */
    'schema' => [
        'simple_array' => true,
    ],


    /*
    |--------------------------------------------------------------------------
    | Optional configurations
    |--------------------------------------------------------------------------
    |
    | Optional configurations
    |
    */
    'options' => [
        // Allows only application/json for request
        'only_json_request' => false,
    ],


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    |
    | Custom HTTP response status for any methods.
    |
    */
    "status" => [
//        "GET" => [
//            200 => [
//                "description" => "Successful Operation",
//            ],
//            404 => [
//                "description" => "Not Found"
//            ]
//        ],
//        "POST" => [
//            200 => [
//                "description" => "Successful Operation",
//            ],
//            422 => [
//                "description" => "Validation Issues"
//            ]
//        ],
//        "PUT" => [
//            200 => [
//                "description" => "Successful Operation",
//            ],
//            404 => [
//                "description" => "Not Found"
//            ],
//            422 => [
//                "description" => "Validation Issues"
//            ]
//        ],
//        "PATCH" => [
//            200 => [
//                "description" => "Successful Operation",
//            ],
//            404 => [
//                "description" => "Not Found"
//            ],
//            422 => [
//                "description" => "Validation Issues"
//            ]
//        ],
//        "DELETE" => [
//            200 => [
//                "description" => "Successful Operation",
//            ],
//            404 => [
//                "description" => "Page Not Found"
//            ]
//        ],
    ],

];
