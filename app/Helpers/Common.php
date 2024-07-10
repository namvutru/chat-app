<?php

namespace App\Helpers;

if (!function_exists('formatResult')) {
    function formatResult($message = '', bool $success = false, $data = []): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
    }
}
