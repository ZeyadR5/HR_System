<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Attributes that should not be sanitized (passwords, binary data, etc.).
     *
     * @var array<int, string>
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        '_token',
    ];

    /**
     * Handle an incoming request and sanitize string inputs against XSS and control characters.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        if (!empty($input)) {
            $sanitized = $this->sanitizeArray($input);
            $request->merge($sanitized);
        }

        return $next($request);
    }

    /**
     * Recursively clean array values.
     */
    protected function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->except, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitizeString($value);
            }
        }

        return $data;
    }

    /**
     * Sanitize a single string value.
     */
    protected function sanitizeString(string $value): string
    {
        // 1. Remove invisible null bytes and harmful control characters (except newline, tab, carriage return)
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        // 2. Strip HTML tags to prevent stored XSS attacks
        $clean = strip_tags($clean ?? '');

        return $clean;
    }
}
