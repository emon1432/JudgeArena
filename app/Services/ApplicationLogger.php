<?php

namespace App\Services;

use App\Models\ApplicationLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApplicationLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'tokens',
        'api_key',
        'apikey',
        'api_secret',
        'secret',
        'secrets',
        'credential',
        'credentials',
        'cookie',
        'session',
        'authorization',
        'bearer',
        'csrf',
    ];

    public function info(string $message, array $context = [], ?Throwable $exception = null): void
    {
        $this->write('info', $message, $context, $exception);
    }

    public function warning(string $message, array $context = [], ?Throwable $exception = null): void
    {
        $this->write('warning', $message, $context, $exception);
    }

    public function error(string $message, array $context = [], ?Throwable $exception = null): void
    {
        $this->write('error', $message, $context, $exception);
    }

    public function critical(string $message, array $context = [], ?Throwable $exception = null): void
    {
        $this->write('critical', $message, $context, $exception);
    }

    private function write(string $level, string $message, array $context = [], ?Throwable $exception = null): void
    {
        $category = (string) ($context['category'] ?? 'system');
        $platform = $this->extractStringContextValue($context, ['platform']);
        $entityType = $this->extractStringContextValue($context, ['entity_type', 'entityType']);
        $entityId = $this->extractStringContextValue($context, ['entity_id', 'entityId']);
        $source = (string) ($context['source'] ?? $this->resolveSource());
        $sanitizedContext = $this->sanitizeContext($context);

        if ($exception !== null) {
            $sanitizedContext['exception'] = [
                'message' => $exception->getMessage(),
                'class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        Log::{$level}($message, $sanitizedContext);

        try {
            ApplicationLog::create([
                'level' => $level,
                'category' => $category,
                'platform' => $platform,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'message' => $message,
                'context' => $sanitizedContext,
                'source' => $source,
                'user_id' => Auth::id(),
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (Throwable $databaseException) {
            Log::error('Failed to persist application log', [
                'message' => $databaseException->getMessage(),
                'exception' => $databaseException::class,
                'file' => $databaseException->getFile(),
                'line' => $databaseException->getLine(),
            ]);
        }
    }

    private function sanitizeContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $context[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $context[$key] = $this->sanitizeContext($value);
            }
        }

        return $context;
    }

    private function extractStringContextValue(array $context, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $context)) {
                continue;
            }

            $value = $context[$key];
            if ($value === null || $value === '') {
                return null;
            }

            return (string) $value;
        }

        return null;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalizedKey = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if ($normalizedKey === $sensitiveKey || str_contains($normalizedKey, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    private function resolveSource(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        $frame = $trace[3] ?? $trace[2] ?? $trace[1] ?? [];

        if (isset($frame['class'], $frame['function'])) {
            return $frame['class'] . '::' . $frame['function'];
        }

        if (isset($frame['file'], $frame['line'])) {
            return basename($frame['file']) . ':' . $frame['line'];
        }

        return 'unknown';
    }
}
