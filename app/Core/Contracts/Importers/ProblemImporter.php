<?php

namespace App\Core\Contracts\Importers;

interface ProblemImporter
{
    public function import(): array;
}
