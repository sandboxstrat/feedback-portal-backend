<?php 

return [       
    'paths' => ['*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Access-Control-Request-Headers','Authorization','content-type','Access-Control-Allow-Origin'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
]

?>