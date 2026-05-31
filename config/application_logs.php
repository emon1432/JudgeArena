<?php

return [
    'retention_days' => env('APPLICATION_LOG_RETENTION_DAYS', 90),
    'critical_retention_days' => env('APPLICATION_LOG_CRITICAL_RETENTION_DAYS', 365),
];
