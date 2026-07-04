<?php

namespace App\Core\Contracts\Importers;

interface StandingsImporter
{
    public function import(): array;
}
