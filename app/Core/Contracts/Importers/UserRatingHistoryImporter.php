<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface UserRatingHistoryImporter
{
    public function import(?string $handle = null): ImportResult;
}
