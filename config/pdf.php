<?php

return [
    'password_min_length' => env('PDF_PASSWORD_MIN_LENGTH', 8),
    'max_attempts_per_minute' => env('PDF_MAX_ATTEMPTS_PER_MINUTE', 3),
    'rate_limit_decay' => env('PDF_RATE_LIMIT_DECAY', 300),
    'watermark_enabled' => env('PDF_WATERMARK_ENABLED', true),
    'security_headers' => [
        'Content-Type' => 'application/pdf',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
    ]
];