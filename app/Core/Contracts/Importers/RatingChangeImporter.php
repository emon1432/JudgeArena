<?php

namespace App\Core\Contracts\Importers;

use App\Core\Results\ImportResult;

interface RatingChangeImporter
{
    public function import(): ImportResult;
}
