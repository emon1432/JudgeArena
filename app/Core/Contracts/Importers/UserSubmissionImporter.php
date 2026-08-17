<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface UserSubmissionImporter
{
    public function import(?string $handle = null, bool $full = false): ImportResult;
}
