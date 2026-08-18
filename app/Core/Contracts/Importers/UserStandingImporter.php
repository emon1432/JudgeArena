<?php

declare(strict_types=1);

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface UserStandingImporter
{
    /**
     * Import user standings for the specified handle or all active profiles.
     *
     * @param string|null $handle
     * @return ImportResult
     */
    public function import(?string $handle = null): ImportResult;
}
