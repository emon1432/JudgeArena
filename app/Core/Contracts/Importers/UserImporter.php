<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface UserImporter
{
    public function import(): ImportResult;
}
