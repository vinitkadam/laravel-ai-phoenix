<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Phoenix Endpoint
    |--------------------------------------------------------------------------
    |
    | The OTLP/HTTP endpoint for your Phoenix instance. Use the Arize-hosted
    | cloud URL, or point at a self-hosted Phoenix server.
    |
    |   Cloud:       https://app.phoenix.arize.com/v1/traces
    |   Self-hosted: http://localhost:6006/v1/traces
    |
    */

    'endpoint' => env('PHOENIX_ENDPOINT', 'https://app.phoenix.arize.com/v1/traces'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Required for Arize-hosted Phoenix. Not needed for self-hosted instances
    | unless you have enabled authentication.
    |
    */

    'api_key' => env('PHOENIX_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Project Name
    |--------------------------------------------------------------------------
    |
    | Groups traces under a named project in the Phoenix UI. Defaults to your
    | application name.
    |
    */

    'project' => env('PHOENIX_PROJECT', env('APP_NAME', 'laravel')),

    /*
    |--------------------------------------------------------------------------
    | Export Timeout
    |--------------------------------------------------------------------------
    |
    | Seconds to wait for Phoenix to acknowledge an export before giving up.
    |
    */

    'timeout' => (float) env('PHOENIX_TIMEOUT', 5.0),

];
