<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface StandingImporter
{
    public function import(): ImportResult;
}
