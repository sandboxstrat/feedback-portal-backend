<?php 

$allowedOrigins = env('CORS_ALLOWED_ORIGINS');

return [       
    'paths' => ['*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [$allowedOrigins],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Access-Control-Request-Headers','Authorization','content-type','Access-Control-Allow-Origin'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
]

?>