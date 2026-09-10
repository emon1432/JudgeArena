<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface ProblemImporter
{
    public function import(?string $contestPlatformId = null, ?int $limit = null): ImportResult;
}
