<?php

return [
    'client_id' => env('ORULO_CLIENT_ID', null),
    'client_secret' => env('ORULO_CLIENT_SECRET', null),
    'redirect_uri' => '',

    'guzzle' => [
        'base_uri' => 'https://www.orulo.com.br/api/v2/',

        // Number of seconds to wait while trying to connect to a server. 0 waits indefinitely.
        'connect_timeout' => 2.0,

        // Time needed to throw a timeout exception after a request is made.
        'timeout' => 0.0,

        // Set this to true if you want to debug the request/response.
        'debug' => true,
    ],

    // Defines if the logging should be enabled or not. If set to true, every the log entry will be sent to Laravel's
    // default logging output.
    'logging' => false,
];
