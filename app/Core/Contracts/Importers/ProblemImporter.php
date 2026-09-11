<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface ProblemImporter
{
    public function import(?int $limit = null, ?callable $onProgress = null): ImportResult;
}
