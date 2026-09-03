<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface ContestImporter
{
    public function import(): ImportResult;
}
