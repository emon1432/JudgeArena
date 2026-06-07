<?php

namespace App\Core\Platforms;

use App\Core\Contracts\Platforms\PlatformAdapter;

class PlatformRegistry
{
    public function resolve(string $slug): ?PlatformAdapter
    {
        $slug = strtolower(trim($slug));

        $adapterClass = config("platforms.{$slug}.adapter");

        if ($adapterClass === null) {
            return null;
        }

        return app($adapterClass);
    }

    public function supportedPlatforms(): array
    {
        return array_keys(config('platforms', []));
    }
}
