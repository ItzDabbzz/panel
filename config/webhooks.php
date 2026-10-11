<?php

return [
    'timeout' => env('APP_WEBHOOK_TIMEOUT', 10),

    'tries' => env('APP_WEBHOOK_TRIES', 3),

    'prune_days' => env('APP_WEBHOOK_PRUNE_DAYS', 30),

    'allow_private_endpoints' => filter_var(env('APP_WEBHOOK_ALLOW_PRIVATE', false), FILTER_VALIDATE_BOOLEAN),
];
