<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface StandingsImporter
{
    public function import(): ImportResult;
}
