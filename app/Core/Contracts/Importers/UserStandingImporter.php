<?php

declare(strict_types=1);

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface UserStandingImporter
{
    /**
     * Import historical user standings for the specified handle.
     *
     * @param string|null $handle
     * @return ImportResult
     */
    public function import(?string $handle = null): ImportResult;
}
