<?php

declare(strict_types=1);

namespace App\Core\Platforms;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Models\Platform;
use Throwable;

class PlatformRegistry
{
    /**
     * Runtime cache of platform slug to official display name.
     *
     * @var array<string, string>
     */
    private static array $nameCache = [];

    public function resolve(string $slug): ?PlatformAdapter
    {
        $slug = strtolower(trim($slug));

        $adapterClass = config("platforms.{$slug}.adapter");

        if ($adapterClass === null) {
            return null;
        }

        return app($adapterClass);
    }

    public function get(string $slug): ?PlatformAdapter
    {
        return $this->resolve($slug);
    }

    public function supportedPlatforms(): array
    {
        return array_keys(config('platforms', []));
    }

    /**
     * Get the official platform display name (e.g. 'Codeforces', 'AtCoder', 'LeetCode').
     * Dynamically resolves from the database Platform model with runtime memory cache.
     */
    public function getPlatformName(string $slug): string
    {
        $normalized = strtolower(trim($slug));

        if (isset(self::$nameCache[$normalized])) {
            return self::$nameCache[$normalized];
        }

        try {
            $name = Platform::query()
                ->where('slug', $normalized)
                ->value('name');

            if (is_string($name) && trim($name) !== '') {
                return self::$nameCache[$normalized] = trim($name);
            }
        } catch (Throwable) {
            // Fallback gracefully if database is not reachable or during isolated unit testing
        }

        // Default formatting if not found in database (e.g. 'codeforces' -> 'Codeforces')
        return self::$nameCache[$normalized] = ucfirst($normalized);
    }
}
