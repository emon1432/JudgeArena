<?php

namespace App\Core\Contracts\Importers;

interface ContestImporter
{
    public function import(): array;
}
